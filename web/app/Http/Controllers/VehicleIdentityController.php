<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleIdentity;
use App\Rules\UniqueBinaryTagIdHex;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VehicleIdentityImport;

class VehicleIdentityController extends Controller
{
    private $perPage;

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $vehicle_identities = VehicleIdentity::paginate($perPage)->appends(['perPage' => $perPage]);

        return view("vehicle-identities.tag_kendaraan", compact("vehicle_identities", "perPage"));
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $perPage = $request->input('perPage', 10);

        if ($search) {
            $vehicle_identities = VehicleIdentity::where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where("id", $search);
                }

                $query->orWhere("type", "ILIKE", "%" . $search . "%")
                    ->orWhereRaw("encode(tag_id, 'hex') ILIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("encode(auth_check, 'hex') ILIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhere("notes", "ILIKE", "%" . $search . "%");
            })->paginate($perPage);
        } else {
            return redirect('/tag_kendaraan');
        }

        return view('vehicle-identities.tag_kendaraan', compact('vehicle_identities', 'search', 'perPage'));
    }

    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file')->store('file_tag_siswa');

        if(!$file){
            return redirect()->route('vehicle-identity.index')->with('error', 'Gagal mengunggah file.');
        }

        try {
            Excel::import(new VehicleIdentityImport, $file);
            return redirect()->route('vehicle-identity.index')->with('success', 'Data tag kendaraan berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->route('vehicle-identity.index')->with('error', 'Ada kolom yang tidak lengkap atau terjadi kesalahan saat import.');
        }
    }

    public function create()
    {
        return view('vehicle-identities.form', ['title' => 'Tambah Tag Kendaraan', 'button' => 'Confirm']);
    }

    public function insert(Request $request)
    {

        $request->validate([
            'tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
            'notes' => 'nullable|string|max:255',
            'auth_check' => 'nullable|max:128',
        ], [
            'tag_id.required' => 'RFID Tag wajib diisi.',
            'notes.max' => 'Note melebihi batas maksimal.',
        ]);

        $vehicle_identity = new VehicleIdentity;

        $vehicle_identity->tag_id = $request->tag_id;
        $vehicle_identity->notes = $request->notes ? $request->notes : " ";
        $vehicle_identity->auth_check = $request->auth_check ? $request->auth_check : null;


        $vehicle_identity->save();

        return redirect('/tag_kendaraan')->with('success', 'Vehicle Tag added successfully.');
    }

    public function edit(VehicleIdentity $vehicle_identity)
    {
        return view('vehicle-identities.form', ['title' => 'Edit Tag Kendaraan', 'button' => 'Save', 'vehicle_identity' => $vehicle_identity]);
    }

    public function update(Request $request, VehicleIdentity $vehicle_identity)
    {
        $request->validate([
            'tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex($vehicle_identity->id)],
            'notes' => 'nullable|string|max:255',
            'auth_check' => 'nullable|max:128',
        ], [
            'tag_id.required' => 'RFID Tag wajib diisi.',  
            'notes.max' => 'Note melebihi batas maksimal.',
        ]);

        $vehicle_identity->update([
            'tag_id' => $request->tag_id,
            'notes' => $request->notes ?  $request->notes : " ",
            'auth_check' => $request->auth_check ? $request->auth_check : null,
        ]);

        return redirect('/tag_kendaraan')->with('success', 'Vehicle Tag updated successfully!');
    }


    public function delete(VehicleIdentity $vehicle_identity)
    {

        if (!$vehicle_identity) {
            return redirect()->back()->with('error', 'Vehicle Tag not found.');
        }

        $vehicle_identity->delete();

        return redirect()->back()->with('success', 'Vehicle Tag deleted successfully.');
    }
}
