<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\PickupPersonnel;
use Illuminate\Validation\Rule;
use App\Models\StudentPickupPersonnelMapping;

class StudentPickupPersonnelMappingController extends Controller
{
    private $perPage;

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $student_pickup_personnel_mappings = StudentPickupPersonnelMapping::join('students', 'student_pickup_personnel_mappings.student_id', '=', 'students.id')
            ->join('pickup_personnels', 'student_pickup_personnel_mappings.pickup_personnel_id', '=', 'pickup_personnels.id')
            ->orderBy('students.class')
            ->orderBy('students.full_name')
            ->select('student_pickup_personnel_mappings.*', 'students.full_name', 'students.class', 'pickup_personnels.full_name')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        return view("student-pickup-personnel-mappings.index", compact("student_pickup_personnel_mappings", "perPage"));
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $perPage = $request->input('perPage', 10);

        if ($search) {
            $student_pickup_personnel_mappings = StudentPickupPersonnelMapping::whereHas('pickup_personnel', function ($query) use ($search) {
                $query->where("full_name", "like", "%" . $search . "%")
                    ->orWhere("phone_number", "like", "%" . $search . "%");
            })
                ->orWhereHas('student', function ($query) use ($search) {
                    $query->where('internal_id', 'like', "%" . $search . "%")
                        ->orWhere('full_name', 'like', "%" . $search . "%")
                        ->orWhere('class', 'like', "%" . $search . "%");
                })
                ->paginate($perPage);
        } else {
            return redirect('/student-pickup-personnel-mapping');
        }

        return view('student-pickup-personnel-mappings.index', compact('student_pickup_personnel_mappings', 'search', 'perPage'));
    }

    public function create(PickupPersonnel $pickup_personnel)
    {
        $mapped_student_ids = $pickup_personnel->student_pickup_personnel_mappings->pluck('student_id')->toArray();
        $students = Student::whereNotIn('id', $mapped_student_ids)
            ->orderBy('class')
            ->orderBy('full_name')
            ->get();

        return view('pickup-personnels.assign', [
            'title' => 'Assign Students for Personnel ' . $pickup_personnel->full_name,
            'button' => 'Assign',
            'pickup_personnel' => $pickup_personnel,
            'students' => $students,
        ]);
    }

    public function insert(Request $request, PickupPersonnel $pickup_personnel)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'relationship_to_student' => 'required'
        ], [
            'student_id.required' => 'Student wajib diisi.',
        ]);

        foreach($pickup_personnel->student_pickup_personnel_mappings as $student_pickup_personnel_mapping){
            if ($request->student_id == $student_pickup_personnel_mapping->student_id){
                return redirect()->route('pickup-personnel.index')->with('error', 'Student is already assign to this pickup personnel.');
            }
        }

        $student_pickup_personnel_mapping = new StudentPickupPersonnelMapping;
        $student_pickup_personnel_mapping->student_id = $request->student_id;
        $student_pickup_personnel_mapping->pickup_personnel_id = $pickup_personnel->id;
        $student_pickup_personnel_mapping->relationship_to_student = $request->relationship_to_student;
        $student_pickup_personnel_mapping->save();

        $previous_url = url()->previous();
        if (strpos($previous_url, route('pickup-personnel.index')) !== false) {
            return redirect()->route('pickup-personnel.index')->with('success', 'Student assigned to pickup personnel successfully.');
        }

        return redirect('/student-pickup-personnel-mapping')->with('success', 'Student assigned to pickup personnel successfully.');
    }

    public function update(Request $request, StudentPickupPersonnelMapping $student_pickup_personnel_mapping)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'relationship_to_student' => 'required|string',
            Rule::unique('student_pickup_personnel_mappings')
                ->where(function ($query) use ($request, $student_pickup_personnel_mapping) {
                    return $query->where('student_id', $request->student_id)
                        ->where('pickup_personnel_id', $student_pickup_personnel_mapping->pickup_personnel_id);
                })
                ->ignore($student_pickup_personnel_mapping->id),
        ], [
            'student_id.required' => 'Student wajib diisi.',
            'student_id.unique' => 'The combination of this student and pickup personnel already exists.'
        ]);

        $student_pickup_personnel_mapping->update([
            'student_id' => $request->student_id,
            'pickup_personnel_id' => $student_pickup_personnel_mapping->pickup_personnel_id,
            'relationship_to_student' => $request->relationship_to_student,
        ]);

        // $previous_url = url()->previous();
        // if (strpos($previous_url, route('pickup-personnel.index')) !== false) { 
        //     return redirect()->route('pickup-personnel.index')->with('success', 'Student assigned to pickup personnel successfully.');
        // }

        return redirect()->route('pickup-personnel.index')->with('success', 'Student assigned to pickup personnel updated successfully.');
    }

    public function delete(StudentPickupPersonnelMapping $student_pickup_personnel_mapping)
    {
        if (!$student_pickup_personnel_mapping) {
            return redirect()->back()->with('error', 'Student and pickup personnel association not found.');
        }

        $student_pickup_personnel_mapping->delete();

        return redirect()->back()->with('success', 'Student and pickup personnel association deleted successfully.');
    }
}
