<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentLink;
use App\Models\DocumentType;
use App\Models\DocumentTypeRequirement;
use App\Models\Query;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
//---------------------------------------------------------------------------//
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'doc_number' => 'required|string|max:255',
            'party_id' => 'nullable|exists:parties,id',
            'remarks' => 'nullable|string',
            'files' => 'required',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {

            $uploadedFiles = $request->file('files');

            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }

            $firstFile = $uploadedFiles[0];

            $fileMode = $firstFile->getClientOriginalExtension() === 'pdf'
                ? 'pdf'
                : 'images';

            $document = Document::create([
                'doc_number' => $validated['doc_number'],
                'file_mode' => $fileMode,
                'document_type_id' => $validated['document_type_id'],
                'outlet_id' => $user->outlet_id,
                'party_id' => $validated['party_id'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'uploaded_by' => $user->id,
                'status' => 'uploaded',
            ]);

            foreach ($uploadedFiles as $index => $file) {

                $path = $file->store(
                    'documents',
                    'public'
                );

                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'sort_order' => $index + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Document uploaded successfully',
                'document_id' => $document->id,
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
//---------------------------------------------------------------------------//
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Document::with([
            'documentType',
            'files'
        ]);

        if ($user->role !== 'admin') {

            $query->where(
                'uploaded_by',
                $user->id
            );
        }

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('document_type_id')) {

            $query->where(
                'document_type_id',
                $request->document_type_id
            );
        }

        if ($request->filled('outlet_id')) {

            $query->where(
                'outlet_id',
                $request->outlet_id
            );
        }

        if ($request->filled('doc_number')) {

            $query->where(
                'doc_number',
                'like',
                '%' . $request->doc_number . '%'
            );
        }

        if ($request->filled('to_date')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }
        
        if ($request->filled('from_date')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('party_id')) {

            $query->where(
                'party_id',
                $request->party_id
            );
        }

        if ($request->filled('link_status')) {

            if ($request->link_status === 'linked') {

                $query->has('links');

            } elseif ($request->link_status === 'unlinked') {

                $query->doesntHave('links');
            }
        }

        $documents = $query
            ->latest()
            ->get();

        return response()->json($documents);
    }
//---------------------------------------------------------------------------//
    public function show(Request $request, Document $document)
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $document->uploaded_by !== $user->id
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $document->load([
            'documentType',
            'files',
            'linkedDocuments.linkedDocument.documentType',
        ]);

        return response()->json($document);
    }
//---------------------------------------------------------------------------//
    public function update(Request $request, Document $document)
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $document->uploaded_by !== $user->id
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if (
            $user->role !== 'admin' &&
            $document->status === 'processed'
        ) {
            return response()->json([
                'message' => 'Processed documents cannot be edited'
            ], 403);
        }

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'doc_number' => 'required|string|max:255',
            'party_id' => 'nullable|exists:parties,id',
            'remarks' => 'nullable|string',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();

        try {

            $document->update([
                'doc_number' => $validated['doc_number'],
                'document_type_id' => $validated['document_type_id'],
                'party_id' => $validated['party_id'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($request->hasFile('files')) {

                $uploadedFiles = $request->file('files');

                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($document->files as $file) {

                    Storage::disk('public')->delete(
                        $file->file_path
                    );
                }

                $document->files()->delete();

                $firstFile = $uploadedFiles[0];

                $document->update([
                    'file_mode' =>
                        $firstFile->getClientOriginalExtension() === 'pdf'
                            ? 'pdf'
                            : 'images'
                ]);

                foreach ($uploadedFiles as $index => $file) {

                    $path = $file->store(
                        'documents',
                        'public'
                    );

                    DocumentFile::create([
                        'document_id' => $document->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Document updated successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
//---------------------------------------------------------------------------//
    public function download(Request $request, Document $document)
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $document->uploaded_by !== $user->id
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $document->load('files');

        $files = $document->files->map(function ($file) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'url' => asset('storage/' . $file->file_path),
            ];
        });

        return response()->json($files);
    }
//---------------------------------------------------------------------------//
    public function link(Request $request, Document $document)
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $document->uploaded_by !== $user->id
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'linked_document_id' => 'required|exists:documents,id',
        ]);

        if ($document->id == $validated['linked_document_id']) {
            return response()->json([
                'message' => 'Document cannot be linked to itself'
            ], 422);
        }

        $alreadyExists = DocumentLink::where(
            'document_id',
            $document->id
        )->where(
            'linked_document_id',
            $validated['linked_document_id']
        )->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Documents already linked'
            ], 422);
        }

        DocumentLink::create([
            'document_id' => $document->id,
            'linked_document_id' => $validated['linked_document_id'],
        ]);

        return response()->json([
            'message' => 'Documents linked successfully'
        ]);
    }
//---------------------------------------------------------------------------//
    public function addRequirement(Request $request,DocumentType $documentType) 
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'required_document_type_id' =>
                'required|exists:document_types,id',
        ]);

        DocumentTypeRequirement::create([
            'document_type_id' => $documentType->id,
            'required_document_type_id' =>
                $validated['required_document_type_id'],
        ]);

        return response()->json([
            'message' => 'Requirement added successfully'
        ]);
    }
//---------------------------------------------------------------------------//
    public function validateLinks(Request $request, Document $document)
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $document->uploaded_by !== $user->id
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $requirements = DocumentTypeRequirement::where(
            'document_type_id',
            $document->document_type_id
        )->with('requiredDocumentType')->get();

        $missing = [];

        foreach ($requirements as $requirement) {

            $exists = $document->linkedDocuments()
                ->whereHas('linkedDocument', function ($query) use ($requirement) {

                    $query->where(
                        'document_type_id',
                        $requirement->required_document_type_id
                    );
                })
                ->exists();

            if (!$exists) {
                $missing[] = $requirement
                    ->requiredDocumentType
                    ->name;
            }
        }

        return response()->json([
            'document_id' => $document->id,
            'valid' => count($missing) === 0,
            'missing' => $missing,
        ]);
    }
//---------------------------------------------------------------------------//
    public function missingLinks(Request $request)
    {
        $user = $request->user();

        $query = Document::with([
            'documentType',
            'linkedDocuments.linkedDocument'
        ]);

        if ($user->role !== 'admin') {
            $query->where('uploaded_by', $user->id);
        }

        $documents = $query->get();

        $result = [];

        foreach ($documents as $document) {

            $requirements = DocumentTypeRequirement::where(
                'document_type_id',
                $document->document_type_id
            )->with('requiredDocumentType')->get();

            $missing = [];

            foreach ($requirements as $requirement) {

                $exists = $document->linkedDocuments()
                    ->whereHas(
                        'linkedDocument',
                        function ($query) use ($requirement) {

                            $query->where(
                                'document_type_id',
                                $requirement->required_document_type_id
                            );
                        }
                    )
                    ->exists();

                if (!$exists) {
                    $missing[] =
                        $requirement->requiredDocumentType->name;
                }
            }

            if (!empty($missing)) {

                $result[] = [
                    'document_id' => $document->id,
                    'doc_number' => $document->doc_number,
                    'document_type' => $document->documentType->name,
                    'missing' => $missing,
                ];
            }
        }

        return response()->json($result);
    }
//---------------------------------------------------------------------------//
    public function process(Request $request,Document $document) 
    {
    $user = $request->user();

    if ($user->role !== 'admin') {
        return response()->json([
            'message' => 'Only admin can process documents'
        ], 403);
    }

    if ($document->status === 'processed') {
        return response()->json([
            'message' => 'Document already processed'
        ], 422);
    }

    $document->update([
        'status' => 'processed'
    ]);

    return response()->json([
        'message' => 'Document processed successfully'
    ]);
    }
//---------------------------------------------------------------------------//
    public function createQuery(Request $request,Document $document) 
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can raise queries'
            ], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        $query = Query::create([
            'document_id' => $document->id,
            'raised_by' => $user->id,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => 'Query created successfully',
            'query' => $query
        ], 201);
    }
//---------------------------------------------------------------------------//
    public function listQueries(Request $request,Document $document) 
    {
        $queries = $document->queries()
            ->with('raisedBy')
            ->latest()
            ->get();

        return response()->json($queries);
    }
//---------------------------------------------------------------------------//
    public function resolveQuery(Request $request,Query $query) 
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can resolve queries'
            ], 403);
        }

        if ($query->resolved_at) {
            return response()->json([
                'message' => 'Query already resolved'
            ], 422);
        }

        $query->update([
            'resolved_at' => now()
        ]);

        return response()->json([
            'message' => 'Query resolved successfully'
        ]);
    }
//---------------------------------------------------------------------------//
    public function destroy(
        Request $request,
        Document $document
    )
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $document->update([
            'deleted_by' => $request->user()->id,
        ]);

        DocumentLink::where(
            'document_id',
            $document->id
        )->delete();

        DocumentLink::where(
            'linked_document_id',
            $document->id
        )->delete();

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }
//---------------------------------------------------------------------------//
    public function deleteFile(
        Request $request,
        DocumentFile $file
    )
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $document = $file->document;

        Storage::disk('public')->delete(
            $file->file_path
        );

        $file->delete();

        if ($document->files()->count() === 0) {

            $document->update([
                'deleted_by' => $request->user()->id,
            ]);

            $document->delete();

            return response()->json([
                'message' => 'Last file removed. Document deleted.',
            ]);
        }

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }
//---------------------------------------------------------------------------//
    public function addFiles(
        Request $request,
        Document $document
    )
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($document->file_mode !== 'images') {
            return response()->json([
                'message' => 'Files can only be added to image documents'
            ], 422);
        }

        $validated = $request->validate([
            'files' => 'required',
            'files.*' => 'file|mimes:jpg,jpeg,png|max:10240',
        ]);

        $lastSortOrder = $document->files()
            ->max('sort_order') ?? 0;

        foreach ($request->file('files') as $index => $file) {

            $path = $file->store(
                'documents',
                'public'
            );

            DocumentFile::create([
                'document_id' => $document->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'sort_order' => $lastSortOrder + $index + 1,
            ]);
        }

        return response()->json([
            'message' => 'Files added successfully',
        ]);
    }
//---------------------------------------------------------------------------//
    public function replaceFile(
        Request $request,
        DocumentFile $file
    )
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        Storage::disk('public')->delete(
            $file->file_path
        );

        $newFile = $request->file('file');

        $path = $newFile->store(
            'documents',
            'public'
        );

        $file->update([
            'file_name' => $newFile->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return response()->json([
            'message' => 'File replaced successfully',
        ]);
    }
//---------------------------------------------------------------------------//
    public function unlink(
        Request $request,
        DocumentLink $link
    )
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $link->delete();

        return response()->json([
            'message' => 'Documents unlinked successfully'
        ]);
    }
//---------------------------------------------------------------------------//
    public function downloadPdf(
        Request $request,
        Document $document
    )
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $document->uploaded_by !== $user->id
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($document->file_mode !== 'images') {
            return response()->json([
                'message' => 'PDF generation only available for image documents'
            ], 422);
        }

        $document->load('files');

        $html = '';

        foreach ($document->files as $file) {

            $path = storage_path(
                'app/public/' . $file->file_path
            );

            $html .= '
                <div style="page-break-after: always;">
                    <img src="' . $path . '" style="width:100%;">
                </div>
            ';
        }

        $pdf = Pdf::loadHTML($html);

        return $pdf->download(
            'document-' . $document->id . '.pdf'
        );
    }
//---------------------------------------------------------------------------//
//---------------------------------------------------------------------------//
}