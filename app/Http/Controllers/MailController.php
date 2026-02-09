<?php

namespace App\Http\Controllers;

use App\Mail\DemoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendMail(){
        $mailData = [
            'title' => 'Mail from Laravel Application',
            'body' => 'This is for testing email using smtp.'
        ];
           
        Mail::to('jaydeepwebdataguru@gmail.com')->queue(new DemoMail($mailData));
             
        dd("Email is sent successfully.");
    }
}
