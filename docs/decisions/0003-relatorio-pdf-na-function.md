---
status: aceito
date: 2026-10-24
---

# Relatório em PDF gerado na function com dompdf e logos em JPEG

## Contexto e problema

O relatório mensal é o documento que o orientador assina, então os números precisam vir do servidor (ARQ-01). O runtime `php-8.4` das Functions não tem a extensão `gd`, e o dompdf precisa dela para processar PNG.

## Opções consideradas

- dompdf na function `timesheet`, com os logos convertidos para JPEG.
- PDF montado no navegador com uma biblioteca JavaScript, a partir do resumo devolvido pela function.
- Function separada só para o PDF.

## Decisão

Opção escolhida: dompdf na `timesheet`, porque mantém o mesmo layout do sistema anterior e o cálculo do mês no servidor, sem dependência nova no site. Os logos vão em JPEG com fundo branco, que o dompdf embute sem `gd`, e o PDF volta em base64 dentro do JSON, porque a execução pela API entrega o corpo como texto.

Quando revisar: se o runtime ganhar `gd`, se o PDF passar de alguns megabytes ou se o relatório precisar de recursos que o dompdf não tem.

### Consequências

- Bom: o PDF é idêntico ao anterior e testado na integração (`%PDF-` no início do arquivo).
- Ruim: logo novo precisa ser JPEG. PNG faz o relatório falhar com `The PHP GD extension is required`.
