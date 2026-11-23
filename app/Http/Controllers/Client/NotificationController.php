<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class NotificationController extends Controller {
 public function index(Request $r){$notifications=DB::table('user_notifications')->where('user_id',$r->user()->id)->latest()->paginate(20);return view('client.notifications',compact('notifications'));}
 public function markRead(Request $r,int $notification){$q=DB::table('user_notifications')->where('id',$notification)->where('user_id',$r->user()->id);abort_unless($q->exists(),404);$q->whereNull('read_at')->update(['read_at'=>now(),'updated_at'=>now()]);return response()->json(['read'=>true]);}
}