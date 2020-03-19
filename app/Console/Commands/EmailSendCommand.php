<?php
/*
 某些监控，发邮件通知


 调用示例：
php artisan email:send

 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailSendCommand extends Command
{
    const NEW_LINE_CHAR = "\r\n";

    protected $name = 'email-send';
    protected $description = '发送监控邮件';

    protected $signature = 'email:send {--r=}';

    public function handle() {
        $options = $this->option();

        $start_time = microtime(true);
        $start_mem = memory_get_usage();
        $this->info(date('Y-m-d H:i:s') . ' begin:');

        $data = $this->main($options);
        $this->info('  result:' . var_export($data, true));

        $end_mem = memory_get_usage();
        $this->info(date('Y-m-d H:i:s') . ' end, cost:' . (microtime(true) - $start_time) . ' seconds! memory_use: ' . ($end_mem - $start_mem) . ' = '. $end_mem . ' - ' . $start_mem );
    }

    private function main($_o) {
        $this->info(date('Y-m-d H:i:s') . ' begin - mbp16inch: ');
        $this->mbp16inch($_o);
        $this->info(date('Y-m-d H:i:s') . ' end mbp16inch');
    }

    // macbook pro 16inch 到货通知
    public function mbp16inch($_o) {
        $a = file_get_contents('https://www.apple.com.cn/shop/refurbished/mac');
        $num = substr_count($a, '16inch');
        if ($num < 2) {
            $this->info(' Macbook Pro 16inch is not for sale!');
            return 0;
        }

        // 出现的次数多，则说明已经开卖了，则需要给我发邮件，抄送多人。
        $request = [
            'email' => 'red_boxer@sohu.com',
            'email_msg' => 'Macbook Pro 16inch have arrived!',
        ];
        $request['cc'] = ['chengfeng@idvert.com', 'chengfeng815142@163.com', 'green_boxer@sina.com'];
        //$request['cc'] = ['red_boxer@sohu.com', 'chengfeng815142@163.com', 'green_boxer@sina.com', 'chengfeng@idvert.com'];
        try {
            Mail::send('emails.macbook_16inch', $request, function ($sendMail) use ($request) {
                if (isset($request['cc']) && $request['cc'])
                    $sendMail->to($request['email'])->cc($request['cc'])->subject('Macbook Pro-16inch到货通知');
                else
                    $sendMail->to($request['email'])->subject('Macbook Pro-16inch-到货通知');
            });
        } catch (\Exception $e) {
            \Log::error('send mail error! ' . print_r(Mail::failures(), true) . ' ' . $e->getMessage());
            return 0;
        }

        return 1;
    }

}


