<?php

// File: app/helpers.php

use Illuminate\Support\Facades\Auth;

if (!function_exists('isPelanggan')) {
    function isPelanggan()
    {
        return Auth::guard('pelanggan')->check() &&
            Auth::guard('pelanggan')->user()->role == 'pelanggan';
    }
}

if (!function_exists('isReseller')) {
    function isReseller()
    {
        return Auth::guard('pelanggan')->check() &&
            Auth::guard('pelanggan')->user()->role == 'reseller';
    }
}

if (!function_exists('isOwner')) {
    function isOwner()
    {
        return Auth::guard('admin')->check() &&
            Auth::guard('admin')->user()->role === 'owner';
    }
}

if (!function_exists('isGudang')) {
    function isGudang()
    {
        return Auth::guard('admin')->check() &&
            Auth::guard('admin')->user()->role === 'gudang';
    }
}

if (!function_exists('isShopkeeper')) {
    function isShopkeeper()
    {
        return Auth::guard('admin')->check() &&
            Auth::guard('admin')->user()->role === 'shopkeeper';
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return Auth::guard('admin')->check();
    }
}

if (!function_exists('isPelangganLoggedIn')) {
    function isPelangganLoggedIn()
    {
        return Auth::guard('pelanggan')->check();
    }
}

if (!function_exists('currentPelanggan')) {
    function currentPelanggan()
    {
        return Auth::guard('pelanggan')->user();
    }
}

if (!function_exists('currentAdmin')) {
    function currentAdmin()
    {
        return Auth::guard('admin')->user();
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole()
    {
        if (isPelangganLoggedIn()) {
            return currentPelanggan()->role;
        }

        if (isAdmin()) {
            return currentAdmin()->role;
        }

        return 'guest';
    }
}
