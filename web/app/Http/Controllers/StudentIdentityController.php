<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\StudentIdentity;
use App\Rules\UniqueBinaryTagId;
use Illuminate\Support\Facades\DB;
use App\Rules\UniqueBinaryTagIdHex;
use App\Imports\StudentIdentityImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentIdentityController extends Controller
{
    private $perPage;

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $student_identities = StudentIdentity::paginate($perPage)->appends(['perPage' => $perPage]);

        return view("student-identities.tag_siswa", compact("student_identities", "perPage"));
    }

    public function search(Request $request)
    {
        $search = $request->search; 
        $perPage = $request->input('perPage', 10);

        if ($search) {
            $student_identities = StudentIdentity::where(function ($query) use ($search) {
                // Check if search is numeric for id comparison
                if (is_numeric($search)) {
                    $query->where("id", $search);
                }

                $query->orWhere("type", "ILIKE", "%" . $search . "%")
                    ->orWhereRaw("encode(tag_id, 'hex') ILIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhere("notes", "ILIKE", "%" . $search . "%");
            })->paginate($perPage);
        } else {
            return redirect('/tag_siswa');
        }

        return view('student-identities.tag_siswa', compact('student_identities', 'search', 'perPage'));
    }

    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file')->store('file_tag_siswa');

        if(!$file){
            return redirect()->route('tag_siswa')->with('error', 'Gagal mengunggah file.');
        }

        try {
            Excel::import(new StudentIdentityImport, $file);
            return redirect()->route('tag_siswa')->with('success', 'Data tag siswa berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->route('tag_siswa')->with('error', 'Ada kolom yang tidak lengkap atau terjadi kesalahan saat import.');
        }
    }

    public function create()
    {
        return view('student-identities.form', ['title' => 'Tambah Tag Siswa', 'button' => 'Confirm']);
    }

    public function insert(Request $request)
    {

        $request->validate([
            'tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
            'notes'=>'nullable|string|max:255',
        ],[
            'tag_id.required'=>'RFID Tag wajib diisi.',
            'notes.max'=>'Note melebihi batas maksimal.',
        ]);

        $student_identity = new StudentIdentity;

        $student_identity->tag_id = $request->tag_id;
        $student_identity->notes = $request->notes ?: " ";

        $student_identity->save();

        return redirect('/tag_siswa')->with('success', 'Student Tag added successfully.');
    }

    public function edit(StudentIdentity $student_identity)
    {
        return view('student-identities.form', ['title' => 'Edit Tag Siswa', 'button' => 'Save', 'student_identity' => $student_identity]);
    }

    public function update(Request $request, StudentIdentity $student_identity)
    {
        $request->validate([
            'tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
            'notes' => 'nullable|string|max:255',
        ], [
            'tag_id.required' => 'RFID Tag wajib diisi.',
            // 'tag_id.size'=>'RFID Tag tidak valid.',
            'notes.max' => 'Note melebihi batas maksimal.',
        ]);

        $student_identity->update([
            'tag_id' => $request->tag_id,
            'notes' => $request->notes ?: " ",
        ]);

        return redirect('/tag_siswa')->with('success', 'Student Tag updated successfully!');
    }


    public function delete(StudentIdentity $student_identity)
    {

        if (!$student_identity) {
            return redirect()->back()->with('error', 'Student Tag not found.');
        }

        $student_identity->delete();

        return redirect()->back()->with('success', 'Student Tag deleted successfully.');
    }
}
