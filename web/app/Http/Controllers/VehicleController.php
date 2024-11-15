<?php

namespace App\Http\Controllers;
use DB;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Vehicle;
use Illuminate\Http\Request; 
use App\Imports\VehicleImport;
use App\Models\VehicleIdentity;
use App\Rules\UniqueBinaryTagIdHex;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StudentVehicleMapping;

class VehicleController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $studentId = $request->input('student_id'); 
        $vehicles = Vehicle::when($studentId, function ($query) use ($studentId) {
                return $query->whereHas('student_vehicle_mappings', function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
    
        $students = Student::orderBy('class')->orderBy('full_name')->get();

        $selectedStudent = $studentId ? Student::find($studentId) : null;
    
        return view("vehicles.kendaraan", compact("vehicles", "perPage", "students", "selectedStudent"));
    }  
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file')->store('file_kendaraan');

        if(!$file){
            return redirect()->route('vehicle.index')->with('error', 'Gagal mengunggah file.');
        }

        try {
            Excel::import(new VehicleImport, $file);
            return redirect()->route('vehicle.index')->with('success', 'Data kendaraan berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->route('vehicle.index')->with('error', 'Ada kolom yang tidak lengkap atau terjadi kesalahan saat import.');
        }
        
    }

    public function search(Request $request)
    {
        $search = $request->search; 
        $perPage = $request->input('perPage', 10);
        $studentId = $request->input('student_id'); 

        $vehicles = Vehicle::query();

        if ($studentId) {
            $vehicles->whereHas('students', function($query) use ($studentId) {
                $query->where('students.id', $studentId);
            });
        }

        if ($search) {
            $vehicles->where(function($query) use ($search) {
                $query->where("type", "ILIKE", "%" . $search . "%") 
                    ->orWhere("model", "ILIKE", "%" . $search . "%") 
                    ->orWhere("color", "ILIKE", "%" . $search . "%") 
                    ->orWhere("license_plate", "ILIKE", "%" . $search . "%") 
                    ->orWhereHas('identities', function($query) use ($search) {
                        $query->whereRaw("encode(tag_id, 'hex') ILIKE ?", ['%' . strtolower($search) . '%']);
                    })
                    ->orWhereHas('students', function($query) use ($search) {
                        $query->where("full_name", "ILIKE", "%" . $search . "%");
                    });
            });
        } else {
            return redirect('/kendaraan');
        }

        $vehicles = $vehicles->paginate($perPage);

        // Get students for filtering options in the view
        $students = Student::orderBy('class')->orderBy('full_name')->get();
        $selectedStudent = $studentId ? Student::find($studentId) : null;

        return view('vehicles.kendaraan', compact('vehicles', 'search', 'perPage', 'students', 'selectedStudent'));
    }

    public function create() {
        $vehicle_identities = VehicleIdentity::whereNull('vehicle_id')->get();
        $students = Student::query()->get();
        return view('vehicles.form', ['vehicle_identities' => $vehicle_identities, 'students' => $students, 'title' => 'Tambah Kendaraan', 'button' => 'Confirm']);
    }

    public function insert(Request $request){

        $request->validate([
            'type' => 'required|string|max:32',
            'model' => 'nullable|string|max:32',
            'color' => 'nullable|string|max:32',
            'license_plate' => 'required|string|max:16',
            'license_plate_expiry' => 'nullable|date_format:m/Y',
            'picture_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ],[
            'type.required' => 'Type wajib diisi.',
            'color.required' => 'Warna wajib diisi.',
            'license_plate.required' => 'Plat nomor wajib diisi.',
            'license_plate_expiry.date_format' => 'Bulan kadaluarsa plat nomor harus berupa bulan yang valid.',
        ]);     

        if($request->filled('new_tag_id')){
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
                'new_auth_check' => 'nullable|max:128',
            ]);
        }

        if($request->filled('student_id')){
            $request->validate([
                'student_id' => 'required|array|min:1',
                'student_id.*' => 'distinct|exists:students,id',
            ]);            
        }

        $vehicle = new Vehicle;
        $vehicle->type = $request->type;
        $vehicle->model = $request->model ? $request->model : null;
        $vehicle->color = $request->color ? $request->color: null;
        $vehicle->license_plate = $request->license_plate;
        $vehicle->license_plate_expiry = $request->license_plate_expiry ? Carbon::createFromFormat('m/Y', $request->license_plate_expiry): null;
        
        if ($request->hasFile('picture_url') && $request->file('picture_url')->isValid()) {
            $picture_name = time().'.'.$request->picture_url->extension();
            $request->picture_url->move(public_path('images'), $picture_name);
            $vehicle->picture_url = 'images/'.$picture_name;
        }   
        $vehicle->save();

        if ($request->filled('vehicle_identity_id') && strtolower($request->vehicle_identity_id) != 'add new') {
            $vehicle_identity = VehicleIdentity::where('id', $request->vehicle_identity_id)->first();
            if ($vehicle_identity) {
                $vehicle_identity->update([
                    'vehicle_id' => $vehicle->id
                ]);
            }
        }

        if($request->filled('new_tag_id')){
            VehicleIdentity::create([
                'tag_id' => $request->new_tag_id,
                'notes' => $request->new_notes ? $request->new_notes : " ",
                'auth_check' => $request->new_auth_check ? $request->new_auth_check : null,
                'vehicle_id' => $vehicle->id,
            ]);
        }

        if($request->filled('student_id')){
            foreach($request->student_id as $student_id){
                StudentVehicleMapping::create([
                    'student_id' => $student_id,
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        }

        return redirect('/kendaraan')->with('success', 'Vehicle added successfully.');
    }

    public function edit(Vehicle $vehicle) {
        $available_tags = VehicleIdentity::whereNull('vehicle_id')->get();
        $assigned_tags = [];
        foreach($vehicle->identities as $identity){
            array_push($assigned_tags, VehicleIdentity::where('id', $identity->id)->first());
        }

        $vehicle_identities = $available_tags->merge($assigned_tags);
        $students = Student::orderBy('class')->orderBy('full_name')->get();

        return view('vehicles.form', [
            'title' => 'Edit Kendaraan', 
            'button' => 'Save', 
            'vehicle' => $vehicle,
            'vehicle_identities' => $vehicle_identities,
            'students' => $students
        ]);
    }

    public function update(Request $request, Vehicle $vehicle){

        $request->validate([
            'type' => 'required|string|max:32',
            'model' => 'nullable|string|max:32',
            'color' => 'required|string|max:32',
            'license_plate' => 'required|string|max:16',
            'license_plate_expiry' => 'nullable|date_format:m/Y',
            'picture_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ],[
            'type.required' => 'Type wajib diisi.',
            'color.required' => 'Warna wajib diisi.',
            'license_plate.required' => 'Plat nomor wajib diisi.',
            'license_plate_expiry.date' => 'Tanggal kadaluarsa plat nomor harus berupa tanggal yang valid.',
        ]); 

        if($request->filled('new_tag_id')){
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
                'new_auth_check' => 'nullable|max:128',
            ]);
        }

        if($request->filled('student_id')){
            $request->validate([
                'student_id' => 'required|array|min:1',
                'student_id.*' => 'distinct|exists:students,id',
            ]);            
        }

         // Handle the image file if it exists and is valid
        if ($request->hasFile('picture_url') && $request->file('picture_url')->isValid()) {
            $picture_name = time().'.'.$request->picture_url->extension();
            // Move the uploaded file to the public/images directory
            $request->picture_url->move(public_path('images'), $picture_name);
            $vehicle->update([
                'picture_url' => $picture_name ? 'images/' . $picture_name : $vehicle->picture_url,
            ]);
        }       

        $vehicle->update([
            'type' => $request->type,
            'model' => $request->model ? $request->model : $vehicle->model,
            'color' => $request->color ? $request->color: $vehicle->color,
            'license_plate' => $request->license_plate,
            $vehicle->license_plate_expiry = $request->license_plate_expiry ? Carbon::createFromFormat('m/Y', $request->license_plate_expiry) : $vehicle->license_plate_expiry,
        ]);

        if($request->filled('vehicle_identity_id') && strtolower($request->vehicle_identity_id) != 'add new'){
            $vehicle_identity = VehicleIdentity::where('id', $request->vehicle_identity_id)->first();
            $vehicle_identity->update([
                'vehicle_id' => $vehicle->id,
            ]);

            foreach($vehicle->identities as $identity){
                if($identity->id != $request->vehicle_identity_id){
                    $identity->update([
                        'vehicle_id' => null,
                    ]);
                }
            }
        }

        if($request->filled('new_tag_id')){
            VehicleIdentity::create([
                'tag_id' => $request->new_tag_id,
                'notes' => $request->new_notes ? $request->new_notes : " ",
                'auth_check' => $request->new_auth_check ? $request->new_auth_check :null,
                'vehicle_id' => $vehicle->id,
            ]);

            foreach($vehicle->identities as $identity){
                if($identity->tag_id != $request->new_tag_id){
                    $identity->update([
                        'vehicle_id' => null,
                    ]);
                }
            }
        }

        if(!$request->filled('student_id')){
            foreach($vehicle->student_vehicle_mappings as $mapping){
                $mapping->delete();
            }
        } else {
            $student_ids = $request->student_id;
            $mappings = $vehicle->student_vehicle_mappings;
        
            foreach ($mappings as $index => $student_vehicle_mapping) {
                if (isset($student_ids[$index])) {
                    $student_vehicle_mapping->update([
                        'student_id' => $student_ids[$index],
                    ]);
                }else{
                    $student_vehicle_mapping->delete();
                }
            }

            for ($i = count($mappings); $i < count($student_ids); $i++) {
                $vehicle->student_vehicle_mappings()->create([
                    'student_id' => $student_ids[$i],
                ]);
            }
        }
     
        return redirect('/kendaraan')->with('success', 'Vehicle updated successfully.');
    }

    public function delete(Vehicle $vehicle)
    {
        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }
    
        $vehicleIdentities = VehicleIdentity::where('vehicle_id', $vehicle->id)->get();
        foreach ($vehicleIdentities as $identity) {
            $identity->vehicle_id = null;
            $identity->save();
        }
    
        $vehicle->delete();
        
        return redirect()->back()->with('success', 'Vehicle deleted successfully.');
    }

    public function assign(Vehicle $vehicle)
    {
        
        $vehicle_identities = VehicleIdentity::whereNull('vehicle_id')->get();

        if (request()->ajax()) {
            return response()->json([
                'vehicle_identities' => $vehicle_identities,
                'vehicle' => $vehicle,
            ]);
        }
        return view('vehicles.assign', [
            'title' => 'Assign Tag',
            'button' => 'Assign',
            'vehicle' => $vehicle,
            'vehicle_identities' => $vehicle_identities
        ]);
    }    

    public function associate(Request $request, Vehicle $vehicle)
    {
        if ($request->filled('new_tag_id') || strtolower($request->tag_id) == 'add new') {
            // Create new student identity tag
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
                'new_auth_check' => 'nullable|max:128',
            ], [
                'new_tag_id.required' => 'RFID Tag wajib diisi.',
                'new_notes.max' => 'Note melebihi batas maksimal.',
            ]);
            $vehilce_identity = new VehicleIdentity;

            $vehilce_identity->tag_id = $request->new_tag_id;
            $vehilce_identity->notes = $request->new_notes ? $request->new_notes : " ";
            //Create association
            $vehilce_identity->vehicle_id = $vehicle->id;

            $vehilce_identity->save();
            return redirect()->back()->with('success', 'New RFID Tag created and assigned successfully.');
        } else {
            $request->validate([
                'tag_id' => 'required|integer',
            ], [
                'tag_id.required' => 'RFID Tag wajib diisi.',
            ]);

            $vehilce_identity = VehicleIdentity::find($request->tag_id);

            if (!$vehilce_identity) {
                return redirect()->back()->with('error', 'RFID Tag not found.');
            }

            $vehilce_identity->update([
                'vehicle_id' => $vehicle->id,
            ]);

            return redirect()->back()->with('success', 'RFID Tag assigned successfully.');
        }
    }
    
    public function edit_associate(VehicleIdentity $vehicle_identity)
    {
        $available_tags = VehicleIdentity::whereNull('vehicle_id')->get();

        $assigned_tags = VehicleIdentity::where('id', $vehicle_identity->id)->get();

        $vehicle_identities = $available_tags->merge($assigned_tags);
        $vehicle = Vehicle::where('id', $vehicle_identity->vehicle_id)->first();

        return response()->json([
            'vehicle_identities' => $vehicle_identities,
            'vehicle_identity' => $vehicle_identity,
            'vehicle' => $vehicle,
        ]);
    }

    public function update_associate(Request $request, VehicleIdentity $vehicle_identity)
    {
        if ($request->filled('new_tag_id') || strtolower($request->tag_id)== 'add new') {
            // Create new student identity tag
            $request->validate([
                'new_tag_id' => ['required', 'max:32', new UniqueBinaryTagIdHex, ""],
                'new_notes' => 'nullable|string|max:255',
                'new_auth_check' => 'nullable|max:128',
            ], [
                'new_tag_id.required' => 'RFID Tag wajib diisi.',
                'new_notes.max' => 'Note melebihi batas maksimal.',
            ]);
            $new_vehicle_identity = new VehicleIdentity;

            $new_vehicle_identity->tag_id = $request->new_tag_id;
            $new_vehicle_identity->notes = $request->new_notes ? $request->new_notes : " ";
            $new_vehicle_identity->auth_check = $request->new_auth_check ? $request->new_auth_check : null;
            //Create association
            $new_vehicle_identity->vehicle_id = $vehicle_identity->vehicle_id;
            $new_vehicle_identity->save();

            $vehicle_identity->update([
                'vehicle_id' => null
            ]);
            return redirect()->back()->with('success', 'New RFID Tag created and assigned successfully.');
        } else {

            $request->validate([
                'tag_id' => 'required|integer',
            ], [
                'tag_id.required' => 'RFID Tag wajib diisi.',
            ]);

            if ($vehicle_identity->id == $request->tag_id) {
                return redirect()->back()->with('success', 'RFID Tag assignment updated successfully.');
            }

            $new_identity = VehicleIdentity::find($request->tag_id);

            if ($new_identity) {
                if ($new_identity->vehicle_id != null) {
                    return redirect()->back()->with('error', 'RFID Tag is already taken by other vehicle.');
                }

                $new_identity->update(['vehicle_id' => $vehicle_identity->vehicle_id]);

                if ($vehicle_identity->id != $request->tag_id) {
                    $vehicle_identity->update(['vehicle_id' => null]);
                }
                return redirect()->back()->with('success', 'RFID Tag assigned to vehicle updated successfully.');
            } else {
                return redirect()->back()->with('error', 'RFID Tag not found.');
            }
        }
    }

    public function disassociate(VehicleIdentity $vehicle_identity)
    {
        if (!$vehicle_identity) {
            return redirect()->back()->with('error', 'RFID Tag not found.');
        }

        $vehicle_identity->update(['vehicle_id' => null]);

        return redirect('/kendaraan')->with('success', 'Tag assigned to vehicle deleted successfully.');
    }
    
    public function edit_associate_siswa(Student $student, Vehicle $vehicle)
    {
        $student_vehicle_mapping = StudentVehicleMapping::where('vehicle_id', $vehicle->id)
            ->where('student_id', $student->id)
            ->first();

       
        $students = Student::orderBy('class')->orderBy('full_name')->get();

        return view('student-vehicle-mappings.assign', [
            'title' => 'Edit Assign Asosiasi Siswa dan Kendaraan',
            'button' => 'Save',
            'vehicle' => $vehicle,
            'students' => $students,
            'student_vehicle_mapping' => $student_vehicle_mapping,
        ]);
    }

    public function disassociate_siswa(Student $student, Vehicle $vehicle)
    {
        $student_vehicle_mapping = StudentVehicleMapping::where('vehicle_id', $vehicle->id)
            ->where('student_id', $student->id)
            ->first();
    
        if (!$student_vehicle_mapping) {
            return redirect()->back()->with('error', 'Student and Vehicle association not found.');
        }
    
        // Menghapus mapping
        $student_vehicle_mapping->delete();
    
        return redirect()->back()->with('success', 'Student and Vehicle association deleted successfully.');
    }    
}   

