<?php

require_once __DIR__ . '/../Classes/customer_class.php';

function login_user_ctr(string $email, string $password): array {
    $email = strtolower(trim($email));
    $password = (string)$password;

    if ($email === '' || $password === '') {
        return ['status'=>'error','message'=>'Email and password are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status'=>'error','message'=>'Invalid email format.'];
    }

    $user = new User();
    $row = $user->authenticate($email, $password);
    if (!$row) {
        return ['status'=>'error','message'=>'Incorrect email or password.'];
    }

    return [
        'status' => 'success',
        'message'=> 'Login successful.',
        'user'   => [
            'customer_id'     => (int)$row['customer_id'],
            'customer_name'   => $row['customer_name'],
            'customer_email'  => $row['customer_email'],
            'user_role'       => (int)$row['user_role'],
            'customer_country'=> $row['customer_country'] ?? null,
            'customer_city'   => $row['customer_city'] ?? null,
            'customer_contact'=> $row['customer_contact'] ?? null,
        ]
    ];
}

function login_customer_ctr(array $k): array {
    return login_user_ctr($k['email'] ?? '', $k['password'] ?? '');
}



function register_user_ctr(
    string $name,
    string $email,
    string $password,
    string $country,
    string $city,
    string $phone_number
): array {
    $user = new User();


    $name    = trim($name);
    $email   = strtolower(trim($email));
    $country = trim($country);
    $city    = trim($city);
    $phone   = trim($phone_number);

    
    if ($name === '' || $email === '' || $password === '' || $country === '' || $city === '' || $phone === '') {
        return ['status'=>'error','message'=>'All fields are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {            
        return ['status'=>'error','message'=>'Invalid email format.'];
    }
    if (!preg_match('/^(?=.*\d).{8,}$/', $password)) {
        return ['status'=>'error','message'=>'Password must be ≥8 characters and include a number.'];
    }

    // Server-enforced role: customer
    $role = 2;

    
    if ($user->emailExists($email)) {
        return ['status'=>'error','message'=>'Email already registered.'];
    }

    
    $new_id = $user->createUser($name, $email, $password, $phone, $role, $country, $city, null);

    if ($new_id) {
        return ['status'=>'success','message'=>'Registration successful.','user_id'=>$new_id];
    }
    return ['status'=>'error','message'=>'Registration failed. Please try again.'];
}


function get_user_by_email_ctr(string $email): ?array {
    $user = new User();
    return $user->getUserByEmail(strtolower(trim($email)));
}


function register_customer_ctr(array $k): array {
    return register_user_ctr(
        $k['name']         ?? '',
        $k['email']        ?? '',
        $k['password']     ?? '',
        $k['country']      ?? '',
        $k['city']         ?? '',
        $k['phone_number'] ?? ''
    );
}
