import { AppwriteException } from 'appwrite';

const messages: Record<string, string> = {
  user_invalid_credentials: 'E-mail ou senha incorretos.',
  user_already_exists: 'Já existe uma conta com este e-mail.',
  user_password_mismatch: 'As senhas não conferem.',
  general_argument_invalid: 'Confira os dados. A senha precisa ter pelo menos 8 caracteres.',
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
