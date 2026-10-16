@extends('errors.layout')

@section('code', '403')
@section('icon', 'bi-shield-lock-fill')
@section('title', 'Akses Dibatasi (403 Forbidden)')
@section('message', $exception->getMessage() ?: 'Maaf, akun Anda tidak memiliki hak akses (otorisasi) untuk membuka modul atau tindakan ini pada Sistem Inventaris Diskominfo Kabupaten Garut.')
