<?php
namespace App\Services;
use App\Models\User;use App\Support\Decimal;use Illuminate\Support\Facades\DB;use RuntimeException;
class LedgerService {
public function account(string $owner,string $code,string $currency,string $normal):int{$r=DB::table('ledger_accounts')->where(['owner_key'=>$owner,'code'=>$code,'currency'=>$currency])->first();if($r)return (int)$r->id;return (int)DB::table('ledger_accounts')->insertGetId(['owner_key'=>$owner,'code'=>$code,'currency'=>$currency,'normal_side'=>$normal,'created_at'=>now(),'updated_at'=>now()]);}
public function wallet(User $u,string $c='USDT'):int{return $this->account('USER:'.$u->id,'PAPER_WALLET',$c,'credit');}
public function system(string $code,string $c='USDT',string $normal='credit'):int{return $this->account('SYSTEM',$code,$c,$normal);}
public function balanceUnits(int $id):int{$sum=0;foreach(DB::table('ledger_entries')->where('ledger_account_id',$id)->get(['side','amount']) as $e){$v=Decimal::units((string)$e->amount);$sum+=$e->side==='credit'?$v:-$v;}return $sum;}
public function post(string $key,string $description,array $entries,array $metadata=[]):int{
if($id=DB::table('ledger_transactions')->where('idempotency_key',$key)->value('id'))return (int)$id;$debit=0;$credit=0;$currency=null;
foreach($entries as $e){$a=DB::table('ledger_accounts')->find($e['account_id']);if(!$a)throw new RuntimeException('Ledger account missing.');$currency??=$a->currency;if($currency!==$a->currency)throw new RuntimeException('Mixed ledger currencies are forbidden.');$v=Decimal::units((string)$e['amount']);if($v<=0)throw new RuntimeException('Ledger amounts must be positive.');if($e['side']==='debit')$debit+=$v;elseif($e['side']==='credit')$credit+=$v;else throw new RuntimeException('Invalid ledger side.');}
if($debit!==$credit||$debit===0)throw new RuntimeException('Unbalanced ledger transaction.');
return DB::transaction(function()use($key,$description,$entries,$metadata){$id=DB::table('ledger_transactions')->insertGetId(['idempotency_key'=>$key,'description'=>$description,'metadata'=>json_encode($metadata,JSON_THROW_ON_ERROR),'posted_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);foreach($entries as $e)DB::table('ledger_entries')->insert(['ledger_transaction_id'=>$id,'ledger_account_id'=>$e['account_id'],'side'=>$e['side'],'amount'=>$e['amount'],'created_at'=>now(),'updated_at'=>now()]);return (int)$id;});
}
public function creditPaperWallet(User $u,string $amount,string $currency='USDT'):int{$wallet=$this->wallet($u,$currency);$capital=$this->system('PAPER_CAPITAL',$currency,'debit');return $this->post('paper-credit:'.$u->id.':'.now()->timestamp.':'.bin2hex(random_bytes(5)),'Administrator-issued simulation credits',[['account_id'=>$capital,'side'=>'debit','amount'=>$amount],['account_id'=>$wallet,'side'=>'credit','amount'=>$amount]],['user_id'=>$u->id,'environment'=>'paper']);}
}
