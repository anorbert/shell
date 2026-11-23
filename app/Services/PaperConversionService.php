<?php
namespace App\Services;
use App\Models\User;
use App\Support\Decimal;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class PaperConversionService {
 public function __construct(private LedgerService $ledger,private MarketQuoteService $markets){}
 public function currencies():array{return array_merge(['USDT'],array_map(fn($symbol)=>substr($symbol,0,-4),config('shell.market.symbols',[])));}
 private function validatePair(string $from,string $to):void{if(!in_array($from,$this->currencies(),true)||!in_array($to,$this->currencies(),true)||$from===$to)throw new RuntimeException('Choose two different supported currencies.');}
 private function price(string $currency):array{if($currency==='USDT')return ['price'=>'1.000000000000','captured_at'=>null];$q=$this->markets->freshQuote($currency.'USDT');return ['price'=>(string)$q->price,'captured_at'=>(string)$q->captured_at];}
 public function quote(string $from,string $to,string $amount):array{
  $this->validatePair($from,$to);$units=Decimal::units($amount);
  if($units<=0||$units>Decimal::units((string)config('shell.paper.max_stake','10000.00000000')))throw new RuntimeException('Conversion amount is outside the paper limits.');
  $source=$this->price($from);$target=$this->price($to);
  $out=BigDecimal::of($amount)->multipliedBy($source['price'])->dividedBy($target['price'],8,RoundingMode::DOWN);
  if($out->isZero()||$out->isNegative())throw new RuntimeException('The estimated output is too small to convert.');
  return ['from_currency'=>$from,'to_currency'=>$to,'amount_in'=>$amount,'amount_out'=>(string)$out,'source_price'=>$source['price'],'target_price'=>$target['price'],'source_quote_at'=>$source['captured_at'],'target_quote_at'=>$target['captured_at']];
 }
 public function convert(User $user,string $from,string $to,string $amount,string $sourceQuoteAt,?string $targetQuoteAt,string $key):object{
  if(!config('shell.paper.enabled'))throw new RuntimeException('Paper conversion is disabled.');
  if($key===''||strlen($key)>100)throw new RuntimeException('A valid request key is required.');
  return DB::transaction(function()use($user,$from,$to,$amount,$sourceQuoteAt,$targetQuoteAt,$key){
   $existing=DB::table('paper_conversions')->where('request_key',$key)->first();
   if($existing){if((int)$existing->user_id!==$user->id)throw new RuntimeException('Request key already used.');if($existing->from_currency!==$from||$existing->to_currency!==$to||Decimal::compare((string)$existing->amount_in,$amount)!==0)throw new RuntimeException('Request key was already used for a different conversion.');return $existing;}
   $quote=$this->quote($from,$to,$amount);
   if(($quote['source_quote_at']??'')!==$sourceQuoteAt||($quote['target_quote_at']??'')!==($targetQuoteAt??''))throw new RuntimeException('Market quote changed. Refresh the preview before confirming.');
   $sourceWallet=$this->ledger->wallet($user,$from);$targetWallet=$this->ledger->wallet($user,$to);
   $sourceClearing=$this->ledger->system('PAPER_FX_CLEARING',$from,'credit');$targetClearing=$this->ledger->system('PAPER_FX_CLEARING',$to,'debit');
   DB::table('ledger_accounts')->whereIn('id',[$sourceWallet,$targetWallet,$sourceClearing,$targetClearing])->orderBy('id')->lockForUpdate()->get();
   $existing=DB::table('paper_conversions')->where('request_key',$key)->first();
   if($existing){if((int)$existing->user_id!==$user->id)throw new RuntimeException('Request key already used.');if($existing->from_currency!==$from||$existing->to_currency!==$to||Decimal::compare((string)$existing->amount_in,$amount)!==0)throw new RuntimeException('Request key was already used for a different conversion.');return $existing;}
   $meta=['user_id'=>$user->id,'request_key'=>$key,'from'=>$from,'to'=>$to,'environment'=>'paper'];
   $this->ledger->post('paper-conversion:'.$key.':source','Paper conversion source debit',[
    ['account_id'=>$sourceWallet,'side'=>'debit','amount'=>$amount],['account_id'=>$sourceClearing,'side'=>'credit','amount'=>$amount]
   ],$meta);
   $this->ledger->post('paper-conversion:'.$key.':target','Paper conversion target credit',[
    ['account_id'=>$targetClearing,'side'=>'debit','amount'=>$quote['amount_out']],['account_id'=>$targetWallet,'side'=>'credit','amount'=>$quote['amount_out']]
   ],$meta);
   $id=DB::table('paper_conversions')->insertGetId(['user_id'=>$user->id,'request_key'=>$key,'from_currency'=>$from,'to_currency'=>$to,'amount_in'=>$amount,'amount_out'=>$quote['amount_out'],'source_price'=>$quote['source_price'],'target_price'=>$quote['target_price'],'source_quote_at'=>$quote['source_quote_at'],'target_quote_at'=>$quote['target_quote_at'],'status'=>'completed','created_at'=>now(),'updated_at'=>now()]);
   DB::table('user_notifications')->insertOrIgnore(['user_id'=>$user->id,'event_key'=>'paper-conversion:'.$id,'type'=>'paper_conversion','title'=>'Paper conversion completed','body'=>'Converted '.$amount.' '.$from.' into '.$quote['amount_out'].' '.$to.'. Simulation only; no cash or external asset moved.','data'=>json_encode(['conversion_id'=>$id],JSON_THROW_ON_ERROR),'created_at'=>now(),'updated_at'=>now()]);
   return DB::table('paper_conversions')->where('id',$id)->first();
  },3);
 }
}