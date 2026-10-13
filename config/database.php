<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
// Konfigurasi Supabase
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://tvfutvuvwxgabanjynjv.supabase.co');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR2ZnV0dnV2d3hnYWJhbmp5bmp2Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTE1MDAzMDAsImV4cCI6MjEwNzA3NjMwMH0.Hs7uPw1BRPqTWS9e0Hcm4EAbDGiHvnHcYVNdMtzDMMM');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR2ZnV0dnV2d3hnYWJhbmp5bmp2Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc5MTUwMDMwMCwiZXhwIjoyMTA3MDc2MzAwfQ.k41d1YK_a-MlF4qu0GSTQKEsH7UvotFNKAGzS8jgJUo');

/**
 * Fungsi untuk melakukan request ke Supabase REST API
 */
function supabase_request($endpoint, $method = 'GET', $data = null, $token = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    } else {
        $headers[] = 'Authorization: Bearer ' . SUPABASE_KEY;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true),
        'raw' => $response
    ];
}

/**
 * Fungsi untuk autentikasi Supabase (login)
 */
function supabase_login($email, $password) {
    $url = SUPABASE_URL . '/auth/v1/token?grant_type=password';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

/**
 * Fungsi untuk register user baru
 */
function supabase_register($email, $password, $name) {
    // Buat user langsung terkonfirmasi (email_confirm = true) via Admin API Supabase
    $url = SUPABASE_URL . '/auth/v1/admin/users';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password,
        'user_metadata' => ['full_name' => $name],
        'email_confirm' => true
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);
    
    // Jika admin endpoint sukses (200 atau 201), user langsung aktif
    if ($httpCode === 200 || $httpCode === 201) {
        return [
            'status' => 200,
            'data' => json_decode($response, true)
        ];
    }
    
    // Fallback ke signup standar jika diperlukan
    $url_signup = SUPABASE_URL . '/auth/v1/signup';
    $ch_signup = curl_init();
    curl_setopt($ch_signup, CURLOPT_URL, $url_signup);
    curl_setopt($ch_signup, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_signup, CURLOPT_POST, true);
    curl_setopt($ch_signup, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch_signup, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password,
        'data' => ['full_name' => $name]
    ]));
    curl_setopt($ch_signup, CURLOPT_SSL_VERIFYPEER, false);
    $res_signup = curl_exec($ch_signup);
    $code_signup = curl_getinfo($ch_signup, CURLINFO_HTTP_CODE);
    // curl_close($ch_signup);
    
    return [
        'status' => $code_signup,
        'data' => json_decode($res_signup, true)
    ];
}
