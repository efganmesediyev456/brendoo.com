<?php

namespace App\Enums;

enum DemandPaymentStatusEnum: int
{
    case Pending = 1;
    case Paid = 2;
    case Rejected = 3;
    case NotRequested  = 4;



   
    public function label(): string
   {
    $locale = app()->getLocale();

    return match ($this) {
        self::Pending => match ($locale) {
            'az' => 'Ödəniş gözlənilir',
            'en' => 'Payment pending',
            'ru' => 'Ожидается оплата',
            default => 'Ödəniş gözlənilir',
        },
        self::Paid => match ($locale) {
            'az' => 'Ödənilib',
            'en' => 'Paid',
            'ru' => 'Оплачено',
            default => 'Ödənilib',
        },
        self::Rejected => match ($locale) {
            'az' => 'Qəbul edilmədi',
            'en' => 'Rejected',
            'ru' => 'Отклонено',
            default => 'Qəbul edilmədi',
        },
        self::NotRequested => match ($locale) {
            'az' => 'Sorğu göndərilməyib',
            'en' => 'Not requested',
            'ru' => 'Запрос не отправлен',
            default => 'Sorğu göndərilməyib',
        },
    };
    }

}
