<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Outlet;
use App\Models\Party;
use App\Models\Document;
use App\Models\Query;
use Illuminate\Http\Request;
use App\Models\User;

class MasterDataController extends Controller
{
    private function ensureAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
//-------------------------------------------------------------------------------------//
    public function documentTypes(Request $request)
    {
        $query = DocumentType::orderBy('name');

        if ($request->user()->role !== 'admin') {
            $query->where('is_active', true);
        }

        return response()->json(
            $query->get()
        );
    }
//-------------------------------------------------------------------------------------//
    public function storeDocumentType(Request $request)
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name',
            'linking_required' => 'required|boolean',
        ]);

        $documentType = DocumentType::create([
            'name' => $validated['name'],
            'linking_required' => $validated['linking_required'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Document type created successfully',
            'data' => $documentType,
        ], 201);
    }
//-------------------------------------------------------------------------------------//    
    public function outlets(Request $request)
    {
        $query = Outlet::orderBy('name');

        if ($request->user()->role !== 'admin') {
            $query->where('is_active', true);
        }

        return response()->json(
            $query->get()
        );
    }
//-------------------------------------------------------------------------------------//
    public function parties(Request $request)
    {
        $query = Party::orderBy('name');

        if ($request->user()->role !== 'admin') {
            $query->where('is_active', true);
        }

        return response()->json(
            $query->get()
        );
    }
//-------------------------------------------------------------------------------------//
    public function dashboard(Request $request)
    {
        $this->ensureAdmin($request);

        $pendingDocuments = Document::with([
            'documentType',
            'uploader',
            'outlet',
            'files'
        ])
        ->where('status', 'uploaded')
        ->latest()
        ->get();

        return response()->json([
            'pending_documents' => Document::where('status', 'uploaded')->count(),

            'query_raised' => Query::whereNull('resolved_at')
                ->distinct('document_id')
                ->count('document_id'),

            'total_processed' => Document::where('status', 'processed')->count(),

            'total_documents' => Document::count(),

            'pending_documents_list' => $pendingDocuments,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function updateDocumentType(Request $request,DocumentType $documentType)
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id,
            'linking_required' => 'required|boolean',
        ]);

        $documentType->update([
            'name' => $validated['name'],
            'linking_required' => $validated['linking_required'],
        ]);

        return response()->json([
            'message' => 'Document type updated successfully',
            'data' => $documentType,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function toggleDocumentType(Request $request, DocumentType $documentType)
    {
        $this->ensureAdmin($request);
        $documentType->update([
            'is_active' => !$documentType->is_active
        ]);

        return response()->json([
            'message' => 'Document type status updated',
            'data' => $documentType,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function storeParty(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:parties,name',
        ]);

        $party = Party::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Party created successfully',
            'data' => $party,
        ], 201);
    }
//-------------------------------------------------------------------------------------//
    public function updateParty(Request $request,Party $party)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:parties,name,' . $party->id,
        ]);

        $party->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Party updated successfully',
            'data' => $party,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function toggleParty(Request $request, Party $party)
    {
        $this->ensureAdmin($request);

        $party->update([
            'is_active' => !$party->is_active
        ]);

        return response()->json([
            'message' => 'Party status updated',
            'data' => $party,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function storeOutlet(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:outlets,name',
        ]);

        $outlet = Outlet::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Outlet created successfully',
            'data' => $outlet,
        ], 201);
    }
//-------------------------------------------------------------------------------------//
    public function updateOutlet(Request $request,Outlet $outlet)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:outlets,name,' . $outlet->id,
        ]);

        $outlet->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Outlet updated successfully',
            'data' => $outlet,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function toggleOutlet(Request $request, Outlet $outlet)
    {
        $this->ensureAdmin($request);

        $outlet->update([
            'is_active' => !$outlet->is_active
        ]);

        return response()->json([
            'message' => 'Outlet status updated',
            'data' => $outlet,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function users(Request $request)
    {
        $this->ensureAdmin($request);
        return response()->json(
            User::with('outlet')
                ->orderBy('name')
                ->get()
        );
    }
//-------------------------------------------------------------------------------------//
    public function storeUser(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,employee',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'outlet_id' => $validated['outlet_id'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }
//-------------------------------------------------------------------------------------//
    public function toggleUser(Request $request, User $user)
    {
        $this->ensureAdmin($request);
        $user->update([
            'is_active' => !$user->is_active
        ]);

        return response()->json([
            'message' => 'User status updated',
            'data' => $user,
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function updateUser(Request $request,User $user)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
            'role' => 'required|in:admin,employee',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'outlet_id' => $validated['outlet_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->load('outlet'),
        ]);
    }
//-------------------------------------------------------------------------------------//
    public function resetPassword(
        Request $request,
        User $user
    )
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'Password reset successfully',
        ]);
    }
//-------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------//

}