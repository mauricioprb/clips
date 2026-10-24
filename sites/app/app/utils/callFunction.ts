import { type ExecutionMethod, ExecutionStatus } from 'appwrite';

export async function callFunction<T>(
  functionId: 'timesheet' | 'members',
  method: ExecutionMethod,
  path: string,
  body?: object,
): Promise<T> {
  const { functions } = useAppwrite();
  const execution = await functions.createExecution({
    functionId,
    method,
    xpath: path,
    body: body ? JSON.stringify(body) : undefined,
    headers: { 'content-type': 'application/json' },
  });

  if (execution.status !== ExecutionStatus.Completed) {
    throw new Error('O Clips não respondeu. Tente de novo em instantes.');
  }

  let data: { error?: string } | null;
  try {
    data = execution.responseBody ? JSON.parse(execution.responseBody) : null;
  } catch {
    throw new Error('O Clips respondeu de forma inesperada. Tente de novo.');
  }

  if (execution.responseStatusCode >= 400) {
    throw new Error(data?.error ?? 'Não foi possível concluir a operação.');
  }

  return data as T;
}
