<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitation extends Notification
{
    public function __construct(
        public readonly string $url,
    ) {}

    /** @return list<string> */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $days = intdiv((int) config('auth.passwords.invitations.expire'), 60 * 24);

        return (new MailMessage)
            ->subject('Seu convite para o Clips')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Você recebeu acesso ao Clips, onde vai registrar suas horas e gerar o relatório mensal da bolsa.')
            ->line('Crie sua senha para aceitar o convite.')
            ->action('Criar minha senha', $this->url)
            ->line("Este convite expira em {$days} dias.")
            ->line('Se você não esperava este convite, ignore esta mensagem.');
    }
}
