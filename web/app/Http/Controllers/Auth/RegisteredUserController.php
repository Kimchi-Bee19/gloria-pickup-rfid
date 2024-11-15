<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    private $perPage;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if this is the first user (admin user)
        $isAdmin = User::count() == 0;

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_approved' => $isAdmin, // Auto-approve first user
        ]);

        // Trigger Registered event
        event(new Registered($user));

        // Log in the newly registered user
        Auth::login($user);

        // Redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function index(Request $request)
    {
        $perPage = $request->input("perPage", 10);
  
        $users = User::where('id', '!=', auth()->id())
                    ->paginate($perPage)
                    ->appends(['perPage' => $perPage]);
    
        return view("admin.admin", compact("users", "perPage"));
    }

    public function search(Request $request)
    {
        $search = $request->search; 
        $perPage = $request->input('perPage', 10);
        
        if ($search) {
            $users = User::where('id', '!=', auth()->id())
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
                })->paginate($perPage)->appends(['search' => $search, 'perPage' => $perPage]);
        } else {
            return redirect('/admin');
        }
       
        return view('admin.admin', compact('users', 'search', 'perPage'));
    }    

    public function approve(User $user)
    {
       
        $user->update([
            'is_approved' => true
        ]);

        return redirect('/admin')->with('success', 'Admin status updated successfully!');

    }

    public function delete(User $user)
    {
        if (!$user) {
            return redirect()->back()->with('error', 'Admin not found.');
        }

        $user->delete();
        
        return redirect()->back()->with('success', 'Admin deleted successfully.');
    }
}