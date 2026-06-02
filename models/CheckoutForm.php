<?php

namespace app\models;

use yii\base\Model;

/**
 * Форма оформления заказа.
 */
class CheckoutForm extends Model
{
    public $email;
    public $first_name;
    public $last_name;
    public $phone;
    public $country = 'Россия';
    public $city;
    public $address;
    public $postal_code;
    public $delivery_method = 'courier';
    public $payment_method = 'card';
    public $comment;

    public function rules(): array
    {
        return [
            [['email', 'first_name', 'last_name', 'phone', 'city', 'address'], 'trim'],
            [['email', 'first_name', 'last_name', 'phone', 'city', 'address'], 'required'],
            ['email', 'email'],
            ['first_name', 'string', 'max' => 100],
            ['last_name', 'string', 'max' => 100],
            ['phone', 'string', 'max' => 32],
            ['country', 'string', 'max' => 64],
            ['city', 'string', 'max' => 128],
            ['address', 'string', 'max' => 512],
            ['postal_code', 'string', 'max' => 32],
            ['delivery_method', 'in', 'range' => ['courier', 'pickup', 'post']],
            ['payment_method', 'in', 'range' => ['card']],
            ['comment', 'string', 'max' => 2000],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'phone' => 'Телефон',
            'country' => 'Страна',
            'city' => 'Город',
            'address' => 'Адрес доставки',
            'postal_code' => 'Почтовый индекс',
            'delivery_method' => 'Способ доставки',
            'payment_method' => 'Способ оплаты',
            'comment' => 'Комментарий',
        ];
    }
}
