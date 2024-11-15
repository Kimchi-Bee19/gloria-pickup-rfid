<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\PickupPersonnel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PickupPersonnelImport;
use App\Models\StudentPickupPersonnelMapping;

class PickupPersonnelController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $studentId = $request->input("student_id", null);

        $pickup_personnels = PickupPersonnel::when($studentId, function ($query) use ($studentId) {
            return $query->whereHas('student_pickup_personnel_mappings', function($q) use ($studentId) {
                $q->where('student_id', $studentId);
            });
        })->orderBy('id')->paginate($perPage)->appends(['perPage' => $perPage, 'student_id' => $studentId]);
    
        $students = Student::orderBy('class')->orderBy('full_name')->get();

        $selectedStudent = $studentId ? Student::find($studentId) : null;
    
        return view("pickup-personnels.penjemput", compact("pickup_personnels", "perPage", "students", "selectedStudent"));
    }
    
    public function search(Request $request)
    {
        $search = $request->search; 
        $perPage = $request->input('perPage', 10);
        $studentId = $request->input("student_id", null);
        
        $pickup_personnels = PickupPersonnel::query();

        if ($studentId) {
            $pickup_personnels->whereHas('student_pickup_personnel_mappings', function($q) use ($studentId) {
                $q->where('student_id', $studentId);
            });
        }

        if ($search) {
            $pickup_personnels->where(function($query) use ($search) {
                $query->where("full_name", "ILIKE", "%" . $search . "%")
                    ->orWhere("phone_number", "ILIKE", "%" . $search . "%")
                    ->orWhere("notes", "ILIKE", "%" . $search . "%")
                    ->orWhereHas('student_pickup_personnel_mappings', function($query) use ($search) {
                        $query->where("relationship_to_student", "ILIKE", "%" . $search . "%")
                                ->orWhereHas('student', function($query) use ($search) {
                                    $query->where("full_name", "ILIKE", "%" . $search . "%");
                                });
                    });
            });
        } else {
            return redirect('/penjemput');
        }

        $pickup_personnels = $pickup_personnels->paginate($perPage)->appends(['perPage' => $perPage, 'student_id' => $studentId]);
        
        $students = Student::orderBy('class')->orderBy('full_name')->get();
        $selectedStudent = $studentId ? Student::find($studentId) : null;

        return view('pickup-personnels.penjemput', compact('pickup_personnels', 'search', 'perPage', 'students', 'selectedStudent'));
    }
    
    public function toggleNotif(PickupPersonnel $pickupPersonnel){
        if($pickupPersonnel->receive_notifications){
            
            $pickupPersonnel->update([
                'receive_notifications'=>false,
            ]);
        }else{
            $pickupPersonnel->update([
                'receive_notifications'=>true,
            ]);
        }

        return redirect()->back()->with('success', 'Notification status changed successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);
        $file = $request->file('file')->store('file_penjemput');
        if(!$file){
            return redirect()->route('pickup-personnel.index')->with('error', 'Gagal mengunggah file.');
        }try {
            Excel::import(new PickupPersonnelImport, $file);
            return redirect()->route('pickup-personnel.index')->with('success', 'Data penjemput berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->route('pickup-personnel.index')->with('error', 'Ada kolom yang tidak lengkap atau terjadi kesalahan saat import.');
        }
    }  

    public function create() {
        $students = Student::query()->get();
        return view('pickup-personnels.form', ['title' => 'Tambah Penjemput', 'button' => 'Confirm', 'students' => $students]);
    }

    public function insert(Request $request){

        $request->validate([
            'full_name' => 'required|string|max:256',
            'phone_number' => 'nullable|string|max:16',
            'picture_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:255',
        ]);        

        if($request->filled('student_id')){
            $request->validate([
                'student_id' => 'required|array|min:1',
                'student_id.*' => 'distinct|exists:students,id',
                'relationship_to_student' => ['required','array', function ($attribute, $value, $error) use ($request) {
                    if (count($value) !== count($request->student_id)) {
                        $error('The number of relationships must match the number of student IDs.');
                    }
                }],
                'relationship_to_student.*' => 'required|string|in:ibu,ayah,kakek,nenek,wali',
            ]);        
           
        }

        $pickup_personnel = new PickupPersonnel;
        $pickup_personnel->full_name = $request->full_name;
        $pickup_personnel->phone_number = $request->phone_number ?  $request->phone_number : null;
        $pickup_personnel->notes =  $request->notes ?  $request->notes : null;
        if ($request->hasFile('picture_url') && $request->file('picture_url')->isValid()) {
            $picture_name = time().'.'.$request->picture_url->extension();
            $request->picture_url->move(public_path('images'), $picture_name);
            $pickup_personnel->picture_url = 'images/'.$picture_name;
        }   
        $pickup_personnel->save();

        if($request->filled('student_id')){
            foreach($request->student_id as $index=> $student_id){
                StudentPickupPersonnelMapping::create([
                    'student_id' => $student_id,
                    'pickup_personnel_id' => $pickup_personnel->id,
                    'relationship_to_student' => strtolower($request->relationship_to_student[$index]),
                ]);
            }
        }

        return redirect('/penjemput')->with('success', 'Pickup Personnel added successfully.');
    }

    public function edit(PickupPersonnel $pickup_personnel) {
        
        
        $students = Student::orderBy('class')->orderBy('full_name')->get();
    
        return view('pickup-personnels.form', [
            'title' => 'Edit Penjemput', 
            'button' => 'Save', 
            'pickup_personnel' => $pickup_personnel,
            'students' => $students
        ]);
    }

    public function update(Request $request, PickupPersonnel $pickup_personnel)
    {
        $request->validate([
            'full_name' => 'required|string|max:256',
            'phone_number' => 'nullable|string|max:16',
            'picture_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Ensuring it's an image
            'notes' => 'nullable|string|max:255',
        ]);     
    
        $picture_name = null;
    
        // Handle the image file if it exists and is valid
        if ($request->hasFile('picture_url') && $request->file('picture_url')->isValid()) {
            $picture_name = time().'.'.$request->picture_url->extension();
            // Move the uploaded file to the public/images directory
            $request->picture_url->move(public_path('images'), $picture_name);
        }

        if($request->filled('student_id')){
            $request->validate([
                'student_id' => 'required|array|min:1',
                'student_id.*' => 'distinct|exists:students,id',
                'relationship_to_student' => ['required','array', function ($attribute, $value, $error) use ($request) {
                    if (count($value) !== count($request->student_id)) {
                        $error('The number of relationships must match the number of student IDs.');
                    }
                }],
                'relationship_to_student.*' => 'required|string|in:ibu,ayah,kakek,nenek,wali',
            ]);            
        }
    
        // Update the pickup personnel record
        $pickup_personnel->update([
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number ?: null,
            'picture_url' => $picture_name ? 'images/' . $picture_name : $pickup_personnel->picture_url, // Keep the old URL if no new picture is uploaded
            'notes' =>  $request->notes ?: null,
        ]);

        if(!$request->filled('student_id')){
            foreach($pickup_personnel->student_pickup_personnel_mappings as $mapping){
                $mapping->delete();
            }
        } else {
            $student_ids = $request->student_id;
            $relationship_to_students = $request->relationship_to_student;
            $mappings = $pickup_personnel->student_pickup_personnel_mappings;
            foreach ($mappings as $index => $student_pickup_personnel_mapping) {
                if (isset($student_ids[$index])) {
                    $student_pickup_personnel_mapping->update([
                        'student_id' => $student_ids[$index],
                        'relationship_to_student' => $relationship_to_students[$index],
                    ]);
                }else{
                    $student_pickup_personnel_mapping->delete();
                }
            }

            for ($i = count($mappings); $i < count($student_ids); $i++) {
                $pickup_personnel->student_pickup_personnel_mappings()->create([
                    'student_id' => $student_ids[$i],
                    'relationship_to_student' => $relationship_to_students[$i],
                ]);
            }
        }

        // Redirect with success message
        return redirect('/penjemput')->with('success', 'Pickup personnel updated successfully.');
    }

    public function delete(PickupPersonnel $pickup_personnel)
    {
        if (!$pickup_personnel) {
            return redirect()->back()->with('error', 'PickupPersonnel not found.');
        }
    
        $pickup_personnel->delete();
        
        return redirect()->back()->with('success', 'Pickup personnel deleted successfully.');
    }

    public function edit_associate_siswa(Student $student, PickupPersonnel $pickup_personnel)
    {
        $student_pickup_personnel_mapping = StudentPickupPersonnelMapping::where('pickup_personnel_id', $pickup_personnel->id)
            ->where('student_id', $student->id)
            ->first();

        $students = Student::orderBy('class')->orderBy('full_name')->get();

        return view('pickup-personnels.assign', [
            'title' => 'Edit Assign Asosiasi Siswa dan Penjemput '. $pickup_personnel->full_name,
            'button' => 'Save',
            'students' => $students,
            'student_pickup_personnel_mapping' => $student_pickup_personnel_mapping,
        ]);
    }

    public function disassociate_siswa(Student $student, PickupPersonnel $pickup_personnel)
    {
        $student_pickup_personnel_mapping = StudentPickupPersonnelMapping::where('pickup_personnel_id', $pickup_personnel->id)
            ->where('student_id', $student->id)
            ->first();
    
        if (!$student_pickup_personnel_mapping) {
            return redirect()->back()->with('error', 'Student and pickup personnel association not found.');
        }
        $student_pickup_personnel_mapping->delete();
    
        return redirect()->back()->with('success', 'Student and pickup personnel association deleted successfully.');
    }    

    // public function filter(Request $request)
    // {
    //     $studentId = $request->get('student_id', null);
    //     $perPage = $request->input("perPage", 10); 
    
    //     $pickupPersonnels = PickupPersonnel::with(['students', 'student_pickup_personnel_mappings' => function ($query) {
    //         $query->select('student_id', 'pickup_personnel_id', 'relationship_to_student'); // Ambil kolom yang diperlukan
    //     }])
    //     ->when($studentId, function ($query) use ($studentId) {
    //         return $query->whereHas('student_pickup_personnel_mappings', function ($query) use ($studentId) {
    //             $query->where('student_id', $studentId);
    //         });
    //     })
    //     ->orderBy('id')
    //     ->paginate($perPage)
    //     ->appends(['perPage' => $perPage]);
        
    
    //     return response()->json([
    //         'pickup_personnels' => $pickupPersonnels,
    //         'pagination' => [
    //             'current_page' => $pickupPersonnels->currentPage(),
    //             'last_page' => $pickupPersonnels->lastPage(),
    //             'per_page' => $pickupPersonnels->perPage(),
    //             'total' => $pickupPersonnels->total(),
    //         ],
    //     ]);
    // }    
}
