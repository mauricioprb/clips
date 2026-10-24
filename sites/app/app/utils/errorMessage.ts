import { AppwriteException } from 'appwrite';

const messages: Record<string, string> = {
  user_invalid_credentials: 'E-mail ou senha incorretos.',
  user_blocked: 'Seu acesso está bloqueado. Fale com a coordenação.',
  user_password_mismatch: 'As senhas não conferem.',
  user_invalid_token: 'Este link expirou ou já foi usado. Peça um novo.',
  password_recently_used: 'Escolha uma senha que você ainda não usou.',
  password_pwned: 'Esta senha apareceu em vazamentos de dados. Escolha outra.',
  password_personal_data: 'A senha não pode conter seu nome, e-mail ou telefone.',
  user_email_already_exists: 'Já existe uma conta com este e-mail.',
  user_already_exists: 'Já existe uma conta com este e-mail.',
  membership_already_exists: 'Esta pessoa já faz parte do Clips.',
  membership_downgrade_prohibited: 'O Clips precisa de pelo menos uma pessoa administradora.',
  team_invite_mismatch: 'Este convite é de outra conta. Saia e abra o link de novo.',
  team_invalid_secret: 'Este convite expirou ou já foi usado. Peça um novo à coordenação.',
  membership_already_confirmed: 'Este convite já foi aceito. Entre com seu e-mail e senha.',
  general_argument_invalid:
    'Confira os dados. A senha precisa ter pelo menos 8 caracteres e não pode ser uma senha comum.',
  general_rate_limit_exceeded: 'Muitas tentativas. Aguarde um pouco e tente de novo.',
};

const fallbackMessage = 'Não foi possível concluir. Tente de novo.';

export function errorMessage(exception: unknown): string {
  if (exception instanceof AppwriteException) {
    return messages[exception.type] ?? fallbackMessage;
  }

  if (exception instanceof Error) {
    return exception.message;
  }

  return fallbackMessage;
}
