<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use setasign\Fpdi\Fpdi;
use App\Models\CostsImput;
use App\Models\User;
use DB;
use ZipArchive;
use Illuminate\Support\Facades\Auth;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Mail\UploadCostsImputsNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ContactMail;



class UploadCostsImputs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filenamewithextension;
    protected $month;
    protected $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filenamewithextension, $month, $year)
    {
        $this->filenamewithextension = $filenamewithextension;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filenamewithextension = $this->filenamewithextension;
        $monthInput = $this->month;
        $yearInput = $this->year;

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile(public_path('storage/media/' . $filenamewithextension));
        $file = pathinfo($filenamewithextension, PATHINFO_FILENAME);

        // Split each page into a new PDF
        for ($i = 1; $i <= $pageCount; $i++) {
            $newPdf = new Fpdi();
            $newPdf->addPage();
            $newPdf->setSourceFile(public_path('storage/media/' . $filenamewithextension));
            $newPdf->useTemplate($newPdf->importPage($i));
            $newFilename = sprintf('%s/%s_%s.pdf', public_path('storage/media/temp'), $file, $i);
            $newPdf->output($newFilename, 'F');
        }

        unlink(public_path('storage/media/' . $filenamewithextension));

        // read and rename each .pdf
        $fileNameNoExt = pathinfo($filenamewithextension, PATHINFO_FILENAME);

        for ($i = 1; $i <= $pageCount; $i++) {
            $path = public_path('storage/media/temp/' . $fileNameNoExt . '_' . $i . '.pdf');
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($path);
            $content = $pdf->getText();

            $findme = 'N.I.F.';
            $pos = strpos($content, $findme);
            $Nif = substr($content, ($pos - 39), 9);

            // check for white spaces, some nif are in different position
            if (ctype_space($Nif[1])) {
                $Nif = substr($content, ($pos - 37), 9);
            }

            $NifFix = preg_replace('/\s+/', '', $Nif);

            $findme2 = 'PERIODO';
            $pos2 = strpos($content, $findme2);
            $month = substr($content, ($pos2 + 15), 2);

            switch ($month) {
                case '01':
                    $month = 'ENE';
                    break;
                case '02':
                    $month = 'FEB';
                    break;
                case '03':
                    $month = 'MAR';
                    break;
                case '04':
                    $month = 'ABR';
                    break;
                case '05':
                    $month = 'MAY';
                    break;
                case '06':
                    $month = 'JUN';
                    break;
                case '07':
                    $month = 'JUL';
                    break;
                case '08':
                    $month = 'AGO';
                    break;
                case '09':
                    $month = 'SEP';
                    break;
                case '10':
                    $month = 'OCT';
                    break;
                case '11':
                    $month = 'NOV';
                    break;
                case '12':
                    $month = 'DIC';
                    break;
            }

            $year = '20' . substr($content, ($pos2 + 18), 2);

            // check if the nif format is correct
            $abc = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Ñ', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            $uploadError = array(null);

            if (in_array($NifFix[0], $abc) || in_array($NifFix[8], $abc)) {
                rename(public_path('storage/media/temp/' . $fileNameNoExt . '_' . $i . '.pdf'), public_path('storage/media/renamedCostsImputs/' . $NifFix . '_' . $month . $year . '_' . $i . '.pdf'));
            } else {
                $uploadError[] = 'El ' . $NifFix . 'ha dado error de forma, consule al administrador de sistema.';
            }
        }

        $files = glob(public_path('storage/media/temp/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // move to month and year folder

        $path = public_path('/storage/media/costsImputs/' . $yearInput);


        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
            $path = public_path('/storage/media/costsImputs/' . $yearInput . '/' . $monthInput);
            File::makeDirectory($path, 0777, true);

            $files = glob(public_path('storage/media/renamedCostsImputs/*'));

            foreach ($files as $file) {
                $filenamewithextension = basename($file);
                $filenamewithoutextension = basename($file, ".pdf");
                $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
                $nif = substr($filename, 0, 9);

                // create user if it doesnt exist

                if (User::where('nif', $nif)->exists()) {
                } else {
                    $user = new User();
                    $user->nif = $nif;
                    $user->name = "Nombre";
                    $user->email = "email@email.com";
                    $password = Str::random(10);
                    $user->password = $password;

                    $data = array(
                        'nif' => $nif,
                        'password' => $password,
                    );

                    Mail::to("raluido@gmail.com")->send(new ContactMail($data));

                    $user->save();
                    $user->assignRole('user');
                }

                if ($monthInput . $yearInput == substr($filename, 10, 7)) {
                    rename(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'), public_path('storage/media/costsImputs/' . $year . '/' . $month . '/' . $filenamewithoutextension . '.pdf'));
                    $costsImput = new CostsImput();
                    $costsImput->user_id = Db::Table('users')->where('nif', $nif)->value('id');
                    $costsImput->filename = $filenamewithoutextension . '.pdf';
                    $costsImput->month = $month;
                    $costsImput->year = $year;
                    $costsImput->save();
                } else {
                    unlink(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'));
                    $uploadError[] = 'Error, mes incorrecto:' . ' ' . $filename;
                }
            }
        } else {
            $path = public_path('/storage/media/costsImputs/' . $year . '/' . $month);

            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true);

                $files = glob(public_path('storage/media/renamedCostsImputs/*'));

                foreach ($files as $file) {
                    $filenamewithextension = basename($file);
                    $filenamewithoutextension = basename($file, ".pdf");
                    $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
                    $nif = substr($filename, 0, 9);

                    // create user if it doesnt exist

                    if (User::where('nif', $nif)->exists()) {
                    } else {
                        $user = new User();
                        $user->nif = $nif;
                        $user->name = "Nombre";
                        $user->email = "email@email.com";
                        $password = Str::random(10);
                        $user->password = $password;

                        $data = array(
                            'nif' => $NifFix,
                            'password' => $password,
                        );

                        Mail::to("raluido@gmail.com")->send(new ContactMail($data));

                        $user->save();
                        $user->assignRole('user');
                    }

                    if ($monthInput . $yearInput == substr($filename, 10, 7)) {
                        rename(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'), public_path('storage/media/costsImputs/' . $year . '/' . $month . '/' . $filenamewithoutextension . '.pdf'));
                        $costsImput = new CostsImput();
                        $costsImput->user_id = Db::Table('users')->where('nif', $nif)->value('id');
                        $costsImput->filename = $filenamewithoutextension . '.pdf';
                        $costsImput->month = $month;
                        $costsImput->year = $year;
                        $costsImput->save();
                    } else {
                        unlink(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'));
                        $uploadError[] = 'Error, mes incorrecto:' . ' ' . $filename;
                    }
                }
            } else {

                $files = glob(public_path('storage/media/renamedCostsImputs/*'));

                foreach ($files as $file) {
                    $filenamewithextension = basename($file);
                    $filenamewithoutextension = basename($file, ".pdf");
                    $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
                    $nif = substr($filename, 0, 9);

                    // create user if it doesnt exist

                    if (User::where('nif', substr($filename, 0, 9))->exists()) {
                    } else {
                        $user = new User();
                        $user->nif = $nif;
                        $user->name = "Nombre";
                        $user->email = "email@email.com";
                        $password = Str::random(10);
                        $user->password = $password;

                        $data = array(
                            'nif' => $NifFix,
                            'password' => $password,
                        );

                        Mail::to("raluido@gmail.com")->send(new ContactMail($data));

                        $user->save();
                        $user->assignRole('user');
                    }

                    if ($monthInput . $yearInput == substr($filename, 10, 7)) {
                        if (File::exists($path . '/' . $filenamewithoutextension . '.pdf')) {
                            rename(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'), public_path('storage/media/costsImputs/' . $year . '/' . $month . '/' . $filenamewithoutextension . '.pdf'));
                            CostsImput::where('filename', $filenamewithoutextension . '.pdf')->delete();
                            $costsImput = new CostsImput();
                            $costsImput->user_id = Db::Table('users')->where('nif', substr($filenamewithoutextension, 0, 9))->value('id');
                            $costsImput->filename = $filenamewithoutextension . '.pdf';
                            $costsImput->month = $month;
                            $costsImput->year = $year;
                            $costsImput->save();
                        } else {
                            rename(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'), public_path('storage/media/costsImputs/' . $year . '/' . $month . '/' . $filenamewithoutextension . '.pdf'));
                            $costsImput = new CostsImput();
                            $costsImput->user_id = Db::Table('users')->where('nif', substr($filenamewithoutextension, 0, 9))->value('id');
                            $costsImput->filename = $filenamewithoutextension . '.pdf';
                            $costsImput->month = $month;
                            $costsImput->year = $year;
                            $costsImput->save();
                        }
                    } else {
                        unlink(public_path('storage/media/renamedCostsImputs/' . $filename . '.pdf'));
                        $uploadError[] = 'Error, mes incorrecto:' . ' ' . $filename;
                    }
                }
            }
        }

        if ($uploadError[0] == null) {
            $uploadError[0] = 'Todos los modelos de imputación de costes se han subido correctamente';
        }

        Mail::to("raluido@gmail.com")->send(new UploadCostsImputsNotification($uploadError));
    }
}
