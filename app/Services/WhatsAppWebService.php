<?php

namespace App\Services;

class WhatsAppWebService
{
    protected string $profileDir;

    public function __construct()
    {
        $this->profileDir = storage_path('app/whatsapp-profile');
        if (! is_dir($this->profileDir)) {
            mkdir($this->profileDir, 0755, true);
        }
    }

    /**
     * يشغّل أمر شل خارجي باستخدام proc_open
     */
    protected function runCommand(string $cmd): void
    {
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {
            // أغلق الأنابيب فوراً لأننا لا نحتاج لقراءة المخرجات
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
        }
    }

    /**
     * يرسل ملفًا عبر WhatsApp Web عن طريق xdotool و Chromium
     *
     * @param string $phone رقم المستلم (بصيغة دولية، مثلاً "20123...")
     * @param string $filePath مسار الملف على الخادم
     */
    public function sendDocument(string $phone, string $filePath): void
    {
        // 1) افتح كروميوم مع ملف بروفايل ثابت
        $url = "https://web.whatsapp.com/send?phone={$phone}&app_absent=0";
        $this->runCommand("nohup chromium-browser --user-data-dir={$this->profileDir} '{$url}' > /dev/null 2>&1 &");

        // 2) انتظر تحميل صفحة WhatsApp Web
        sleep(15);

        // 3) فعّل نافذة كروميوم ثم استخدم xdotool للنقر وكتابة مسار الملف
        $this->runCommand("DISPLAY=:0 xdotool search --onlyvisible --class chromium windowactivate");
        $this->runCommand("DISPLAY=:0 xdotool mousemove 100 100 click 1");   // انقر على أيقونة المرفقات
        sleep(2);

        $this->runCommand("DISPLAY=:0 xdotool type '{$filePath}'");           // اكتب مسار الملف
        $this->runCommand("DISPLAY=:0 xdotool key Return");                  // اضغط Enter لاختيار الملف
        sleep(3);

        $this->runCommand("DISPLAY=:0 xdotool mousemove 1200 800 click 1");  // انقر زر الإرسال
        sleep(5);

        // 4) أغلق التبويب
        $this->runCommand("DISPLAY=:0 xdotool key ctrl+w");
    }
}
