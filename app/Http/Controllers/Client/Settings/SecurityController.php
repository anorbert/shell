<?php
namespace App\Http\Controllers\Client\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class SecurityController extends Controller {
 public function index(Request $r){
  $databaseSessions=config('session.driver')==='database';
  $sessions=$databaseSessions?DB::table('sessions')->where('user_id',$r->user()->id)->orderByDesc('last_activity')->get():collect();
  $loginEvents=DB::table('user_login_events')->where('user_id',$r->user()->id)->latest('created_at')->limit(25)->get();
  return view('client.settings.security',['sessions'=>$sessions,'loginEvents'=>$loginEvents,'databaseSessions'=>$databaseSessions,'currentSessionId'=>$r->session()->getId()]);
 }
 public function revokeOthers(Request $r){
  $r->validate(['password'=>['required','string']]);
  if(!Hash::check($r->input('password'),$r->user()->password))throw ValidationException::withMessages(['password'=>'The password is incorrect.']);
  abort_unless(config('session.driver')==='database',409,'Session revocation requires the database session driver.');
  DB::table('sessions')->where('user_id',$r->user()->id)->where('id','!=',$r->session()->getId())->delete();
  $r->user()->forceFill(['remember_token'=>Str::random(60)])->save();
  return response()->json(['message'=>'Other sessions and remembered-device tokens have been revoked.']);
 }
 public function revoke(Request $r,string $session){
  abort_unless(config('session.driver')==='database',409,'Session revocation requires the database session driver.');
  abort_if(hash_equals($r->session()->getId(),$session),422,'You cannot revoke the current session from this screen.');
  $deleted=DB::table('sessions')->where('id',$session)->where('user_id',$r->user()->id)->delete();
  abort_unless($deleted===1,404);
  $r->user()->forceFill(['remember_token'=>Str::random(60)])->save();
  return response()->json(['message'=>'Session revoked. Remembered-device tokens were also rotated.']);
 }
}