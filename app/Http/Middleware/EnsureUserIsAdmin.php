<?php
namespace App\Http\Middleware;
use Closure;use Illuminate\Http\Request;use Illuminate\Support\Facades\DB;use Symfony\Component\HttpFoundation\Response;
class EnsureUserIsAdmin {
 public function handle(Request $r,Closure $next):Response{abort_unless($r->user()&&$r->user()->role==='admin',403);DB::table('admin_audit_logs')->insert(['actor_user_id'=>$r->user()->id,'action'=>'admin.page_access','method'=>$r->method(),'path'=>substr($r->path(),0,255),'ip_address'=>$r->ip(),'user_agent'=>substr((string)$r->userAgent(),0,5000),'created_at'=>now()]);return $next($r);}
}