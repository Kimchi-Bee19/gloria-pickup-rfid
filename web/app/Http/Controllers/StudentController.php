<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\StudentIdentity;
use App\Rules\UniqueBinaryTagIdHex;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $classFilter = $request->input('class'); 

        $students = Student::when($classFilter, function ($query) use ($classFilter) {
                return $query->where('class', $classFilter);
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage, 'class' => $classFilter]); 

        $student_identities = StudentIdentity::whereNull('student_id')->get();
        $classes = Student::select('class')->distinct()->orderBy('class')->pluck('class');

        return view("students.siswa", compact("students", "perPage", "student_identities", "classes", "classFilter"));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file')->store('file_siswa');

        if(!$file){
            return redirect()->route('siswa')->with('error', 'Gagal mengunggah file.');
        }

        try {
            Excel::import(new StudentImport, $file);
            return redirect()->route('siswa')->with('success', 'Data siswa berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->route('siswa')->with('error', 'Ada kolom yang tidak lengkap atau terjadi kesalahan saat import.');
        }
        
    }

    public function search(Request $request)
    {
        $search = $request->input('search'); 
        $perPage = $request->input('perPage', 10);
        $classFilter = $request->input('class'); 
    
        $studentsQuery = Student::query();
    
        if ($classFilter) {
            $studentsQuery->where('class', $classFilter);
        }
    
        if ($search) {
            $studentsQuery->where(function($query) use ($search) {
                $query->where('internal_id', 'ILIKE', '%' . $search . '%')
                    ->orWhere('full_name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('call_name', 'ILIKE', '%' . $search . '%')
                    ->orWhereHas('identities', function($subQuery) use ($search) {
                        $subQuery->whereRaw("encode(tag_id, 'hex') ILIKE ?", ['%' . strtolower($search) . '%']);
                    });
            });
        } else {
            return redirect('/siswa');
        }
    
        $students = $studentsQuery->paginate($perPage);
        
        $classes = Student::select('class')->distinct()->orderBy('class')->pluck('class');
    
        return view('students.siswa', compact('students', 'search', 'perPage', 'classes', 'classFilter'));
    }

    public function create() {
        $student_identities = StudentIdentity::whereNull('student_id')->get();
        $students = Student::query()->get();
        return view('students.form', ['title' => 'Tambah Siswa', 'button' => 'Confirm', 'student_identities' => $student_identities, 'students' => $students]);
    }

    public function insert(Request $request){
        $request->validate([
            'internal_id' => 'nullable|string|max:16',
            'full_name' => 'required|string|max:256',
            'call_name' => 'nullable|string|max:64',
            'class' => 'nullable|string|max:32',
            'picture_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);   
        
        if($request->filled('new_tag_id')){
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
            ]);
        }

        $student = new Student;
        $student->full_name = $request->full_name;
        $student->call_name = $request->call_name ?  $request->call_name : null;
        $student->internal_id = $request->internal_id ?  $request->internal_id : null;
        $student->class = $request->class ?  $request->class : null;
        $student->picture_url = $request->picture_url? $request->picture_url : null;
        if ($request->hasFile('picture_url') && $request->file('picture_url')->isValid()) {
            $picture_name = time().'.'.$request->picture_url->extension();
            $request->picture_url->move(public_path('images'), $picture_name);
            $student->picture_url = 'images/'.$picture_name;
        }   
        $student->save();

        if ($request->filled('student_identity_id') && strtolower($request->student_identity_id) != 'add new') {
            $student_identity = StudentIdentity::where('id', $request->student_identity_id)->first();
            if ($student_identity) {
                $student_identity->update([
                    'student_id' => $student->id
                ]);
            }
        }

        if($request->filled('new_tag_id')){
            StudentIdentity::create([
                'tag_id' => $request->new_tag_id,
                'notes' => $request->new_notes ? $request->new_notes : " ",
                'student_id' => $student->id,
            ]);
        }


        return redirect('/siswa')->with('success', 'Student added successfully.');
    }

    public function edit(Student $student) {

        $available_tags = StudentIdentity::whereNull('student_id')->get();
        $assigned_tags = [];
        foreach($student->identities as $identity){
            array_push($assigned_tags, StudentIdentity::where('id', $identity->id)->first());
        }

        $student_identities = $available_tags->merge($assigned_tags);
        return view('students.form', [
            'title' => 'Tambah Siswa', 
            'button' => 'Save', 
            'student' => $student,
            'student_identities' => $student_identities,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'internal_id' => 'nullable|string|max:16',
            'full_name' => 'required|string|max:256',
            'call_name' => 'nullable|string|max:64',
            'class' => 'nullable|string|max:32',
            'picture_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);        

        
        if($request->filled('new_tag_id')){
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
            ]);
        }

    
        $picture_name = null;
    
        // Handle the image file if it exists and is valid
        if ($request->hasFile('picture_url') && $request->file('picture_url')->isValid()) {
            $picture_name = time().'.'.$request->picture_url->extension();
            // Move the uploaded file to the public/images directory
            $request->picture_url->move(public_path('images'), $picture_name);
        }
    
        $student->update([
            'full_name' => $request->full_name,
            'call_name' => $request->call_name ?  $request->call_name : null,
            'internal_id' => $request->internal_id ?  $request->internal_id : null,
            'class' => $request->class ?  $request->class : null,
            'picture_url' => $picture_name ? 'images/' . $picture_name : $student->picture_url, // Keep the old URL if no new picture is uploaded
        ]);

        if($request->filled('student_identity_id') && strtolower($request->student_identity_id) != 'add new'){
            $student_identity = StudentIdentity::where('id', $request->student_identity_id)->first();
            $student_identity->update([
                'student_id' => $student->id,
            ]);

            foreach($student->identities as $identity){
                if($identity->id != $request->student_identity_id){
                    $identity->update([
                        'student_id' => null,
                    ]);
                }
            }
        }

        if($request->filled('new_tag_id')){
            StudentIdentity::create([
                'tag_id' => $request->new_tag_id,
                'notes' => $request->new_notes ? $request->new_notes : " ",
                'student_id' => $student->id,
            ]);

            foreach($student->identities as $identity){
                if($identity->tag_id != $request->new_tag_id){
                    $identity->update([
                        'student_id' => null,
                    ]);
                }
            }
        }
    
        // Redirect with success message
        return redirect()->back()->with('success', 'Student updated successfully.');
    }

    public function delete(Student $student)
    {
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }
    
        $student->delete();
        
        return redirect()->back()->with('success', 'Student deleted successfully.');
    }
    

    public function assign(Student $student)
    {
        $student_identities = StudentIdentity::whereNull('student_id')->get();

        if (request()->ajax()) {
            return response()->json([
                'student_identities' => $student_identities,
                'student' => $student,
            ]);
        }
        return view('students.assign', [
            'title' => 'Assign Tag',
            'button' => 'Assign',
            'student' => $student,
            'student_identities' => $student_identities
        ]);
    }

    public function associate(Request $request, Student $student)
    {
        if ($request->filled('new_tag_id') || strtolower($request->tag_id) == 'add new') {
            // Create new student identity tag
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
            ], [
                'new_tag_id.required' => 'RFID Tag wajib diisi.',
                'new_notes.max' => 'Note melebihi batas maksimal.',
            ]);
            $student_identity = new StudentIdentity;

            $student_identity->tag_id = $request->new_tag_id;
            $student_identity->notes = $request->new_notes ? $request->new_notes : " ";
            //Create association
            $student_identity->student_id = $student->id;

            $student_identity->save();
            return redirect()->back()->with('success', 'New RFID Tag created and assigned successfully.');
        } else {
            $request->validate([
                'tag_id' => 'required|integer',
            ], [
                'tag_id.required' => 'RFID Tag wajib diisi.',
            ]);

            $student_identity = StudentIdentity::find($request->tag_id);

            if (!$student_identity) {
                return redirect()->back()->with('error', 'RFID Tag not found.');
            }

            $student_identity->update([
                'student_id' => $student->id,
            ]);

            return redirect()->back()->with('success', 'RFID Tag assigned successfully.');
        }
    }

    public function edit_associate(StudentIdentity $student_identity)
    {
        $available_tags = StudentIdentity::whereNull('student_id')->get();

        $assigned_tags = StudentIdentity::where('id', $student_identity->id)->get();

        $student_identities = $available_tags->merge($assigned_tags);
        $student = Student::where('id', $student_identity->student_id)->first();

        // if (request()->ajax()) {
        return response()->json([
            'student_identities' => $student_identities,
            'student_identity' => $student_identity,
            'student' => $student,
        ]);
        // }

        // return view('students.assign', [
        //     'title' => 'Edit Assign Tag',
        //     'button' => 'Save',
        //     'student' => $student_identity->student,
        //     'student_identities' => $student_identities,
        //     'student_identity' => $student_identity
        // ]);
    }

    public function update_associate(Request $request, StudentIdentity $student_identity)
    {
        if ($request->filled('new_tag_id') || strtolower($request->tag_id) == 'add new') {
            // Create new student identity tag
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
            ], [
                'new_tag_id.required' => 'RFID Tag wajib diisi.',
                'new_notes.max' => 'Note melebihi batas maksimal.',
            ]);
            $new_student_identity = new StudentIdentity;

            $new_student_identity->tag_id = $request->new_tag_id;
            $new_student_identity->notes = $request->new_notes ? $request->new_notes : " ";
            //Create association
            $new_student_identity->student_id = $student_identity->student_id;
            $new_student_identity->save();

            $student_identity->update([
                'student_id' => null
            ]);
            return redirect()->back()->with('success', 'New RFID Tag created and assigned successfully.');
        } else {

            $request->validate([
                'tag_id' => 'required|integer',
            ], [
                'tag_id.required' => 'RFID Tag wajib diisi.',
            ]);

            if ($student_identity->id == $request->tag_id) {
                return redirect()->back()->with('success', 'RFID Tag assignment updated successfully.');
            }

            $new_identity = StudentIdentity::find($request->tag_id);

            if ($new_identity) {
                if ($new_identity->student_id != null) {
                    return redirect()->back()->with('error', 'RFID Tag is already taken by other student. ' . $new_identity->student->full_name . " ini.");
                }

                $new_identity->update(['student_id' => $student_identity->student_id]);

                if ($student_identity->id != $request->tag_id) {
                    $student_identity->update(['student_id' => null]);
                }
                return redirect()->back()->with('success', 'RFID Tag assigned to student updated successfully.');
            } else {
                return redirect()->back()->with('error', 'RFID Tag not found.');
            }
        }
    }

    public function disassociate(StudentIdentity $student_identity)
    {
        if (!$student_identity) {
            return redirect()->back()->with('error', 'RFID Tag not found.');
        }

        $student_identity->update(['student_id' => null]);

        return redirect('/siswa')->with('success', 'Tag assigned to student deleted successfully.');
    }

    // public function filter(Request $request)
    // {
    //     $class = $request->get('class', null);
    //     $perPage = $request->input("perPage", 10);

    //     $students = Student::with('identities')
    //         ->when($class, function ($query, $class) {
    //             return $query->where('class', $class);
    //         })
    //         ->orderBy('class')
    //         ->paginate($perPage)
    //         ->appends(['perPage' => $perPage]);

    //     return response()->json([
    //         'students' => $students,
    //         'pagination' => [
    //             'current_page' => $students->currentPage(),
    //             'last_page' => $students->lastPage(),
    //             'per_page' => $students->perPage(),
    //             'total' => $students->total(),
    //             'previous_page' => $students->previousPageUrl(),
    //             'next_page' => $students->nextPageUrl(),
    //         ],
    //     ]);
    // }
}
