<?php

require 'c:/Users/user/Desktop/npontu-technologies-sre/vendor/autoload.php';
$app = require_once 'c:/Users/user/Desktop/npontu-technologies-sre/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
auth()->login($user);

$test = Livewire\Livewire::test(App\Livewire\DailyActivityBoard::class);
$renderedComponent = $test->html();
$renderedLayout = view('layouts.app', ['slot' => new \Illuminate\Support\HtmlString($renderedComponent)])->render();

echo "Length of rendered layout: " . strlen($renderedLayout) . "\n";
echo "Contains 'Daily Activity Board': " . (str_contains($renderedLayout, 'Daily Activity Board') ? 'YES' : 'NO') . "\n";
echo "Contains 'SRE Cockpit': " . (str_contains($renderedLayout, 'SRE Cockpit') ? 'YES' : 'NO') . "\n";
