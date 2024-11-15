<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentVehicleMapping;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class StudentVehicleMappingController extends Controller
{
    private $perPage;

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $student_vehicle_mappings = StudentVehicleMapping::join('students', 'student_vehicle_mappings.student_id', '=', 'students.id')
            ->join('vehicles', 'student_vehicle_mappings.vehicle_id', '=', 'vehicles.id')
            ->orderBy('students.class')
            ->orderBy('students.full_name')
            ->select('student_vehicle_mappings.*', 'students.full_name', 'students.class', 'vehicles.type', 'vehicles.model', 'vehicles.license_plate')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
    
        return view("student-vehicle-mappings.index", compact("student_vehicle_mappings", "perPage"));
    }
    
    public function search(Request $request)
    {
        $search = $request->search; 
        $perPage = $request->input('perPage', 10);

        if ($search) {
            $student_vehicle_mappings = StudentVehicleMapping::whereHas('vehicle', function($query) use ($search) {
                    $query->where("type", "like", "%" . $search . "%")
                        ->orWhere("model", "like", "%" . $search . "%")
                        ->orWhere("color", "like", "%" . $search . "%")
                        ->orWhere("license_plate", "like", "%" . $search . "%");
                })
                ->orWhereHas('student', function($query) use ($search) {
                    $query->where('internal_id', 'like', "%" . $search . "%")
                        ->orWhere('full_name', 'like', "%" . $search . "%")
                        ->orWhere('class', 'like', "%" . $search . "%");
                })
                ->paginate($perPage);
        } 
        else {
            return redirect('/student-vehicle-mapping');
        }

        return view('student-vehicle-mappings.index', compact('student_vehicle_mappings', 'search', 'perPage'));
    }

    public function create(Vehicle $vehicle)
    {
        $assigned_students = collect();

        foreach($vehicle->student_vehicle_mappings as $student_vehicle_mapping){
           $assigned_students ->push($student_vehicle_mapping->student);
        }
        
        $students = Student::orderBy('class')->orderBy('full_name')->get();
        $available_students = $students->diff($assigned_students);
        
        return view('student-vehicle-mappings.assign', [
            'title' => 'Assign Asosiasi Siswa dan Kendaraan',
            'button' => 'Assign',
            'vehicle' => $vehicle,
            'students' => $available_students,
        ]);
    }    

    public function insert(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'student_id' => 'required|integer',
        ],[
            'student_id.required' => 'Student wajib diisi.',
        ]);        

        foreach($vehicle->student_vehicle_mappings as $student_vehicle_mapping){
            if ($request->student_id == $student_vehicle_mapping->student_id){
                return redirect()->route('vehicle.index')->with('error', 'Student is already assign to this vehicle.');
            }
        }

        $student_vehicle_mapping = new StudentVehicleMapping;
        $student_vehicle_mapping->student_id = $request->student_id;
        $student_vehicle_mapping->vehicle_id = $vehicle->id;
        $student_vehicle_mapping->save();

        $previous_url = url()->previous();
        if (strpos($previous_url, route('vehicle.index')) !== false) { 
            return redirect()->route('vehicle.index')->with('success', 'Student assigned to vehicle successfully.');
        }
       
        return redirect('/student-vehicle-mapping')->with('success', 'Student assigned to vehicle successfully.');
    }

    public function edit(StudentVehicleMapping $student_vehicle_mapping)
    {
        $vehicles = Vehicle::orderBy('type')->get();
        $students = Student::orderBy('class')->orderBy('full_name')->get();

        return view('student-vehicle-mappings.assign', [
            'title' => 'Edit Assign Asosiasi Siswa dan Kendaraan',
            'button' => 'Save',
            'vehicles' => $vehicles,
            'students' => $students,
            'student_vehicle_mapping' => $student_vehicle_mapping,
        ]);
    }

    public function update(Request $request, StudentVehicleMapping $student_vehicle_mapping)
    {
        $request->validate([
            'student_id' => 'required|integer',
        ],[
            'student_id.required' => 'Student wajib diisi.',
        ]);       

        $student_vehicle_mapping->update([
            'student_id' => $request->student_id,
            'vehicle_id' => $student_vehicle_mapping->vehicle->id,
        ]);

        $previous_url = url()->previous();
        if (strpos($previous_url, route('vehicle.index')) !== false) { 
            return redirect()->route('vehicle.index')->with('success', 'Student assigned to vehicle successfully.');
        }

        return redirect('/student-vehicle-mapping')->with('success', 'Student assigned to vehicle updated successfully.');
    }

    public function delete(StudentVehicleMapping $student_vehicle_mapping)
    {
        if (!$student_vehicle_mapping) {
            return redirect()->back()->with('error', 'Student and Vehicle association not found.');
        }

        $student_vehicle_mapping->delete();
        
        return redirect()->back()->with('success', 'Student and Vehicle association deleted successfully.');
    }
}