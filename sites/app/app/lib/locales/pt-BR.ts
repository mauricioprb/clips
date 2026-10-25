import type { FuseLocale } from '@/lib/locale';

export const ptBR: FuseLocale = {
  dateLocale: 'pt-BR',
  alert: {
    dismissLabel: 'Fechar',
  },
  appShellSearch: {
    label: 'Buscar',
  },
  appShellSidebar: {
    navigationLabel: 'Navegação principal',
    userMenuLabel: 'Abrir menu do usuário',
  },
  appShellStacked: {
    navigationLabel: 'Navegação principal',
    menuLabel: 'Abrir ou fechar o menu',
    userMenuLabel: 'Abrir menu do usuário',
  },
  appShellUserMenu: {
    menuLabel: 'Abrir menu do usuário',
  },
  authForgotPassword: {
    title: 'Esqueceu a senha?',
    description: 'Sem problema. Vamos enviar as instruções para redefinir a senha.',
    emailLabel: 'E-mail',
    submitLabel: 'Enviar link de redefinição',
    backLabel: 'Voltar para o login',
    emailPlaceholder: 'voce@exemplo.com',
  },
  authLogin: {
    title: 'Entre na sua conta',
    description: 'Que bom ver você de novo. Informe seus dados.',
    emailLabel: 'E-mail',
    passwordLabel: 'Senha',
    rememberLabel: 'Lembrar de mim',
    forgotLabel: 'Esqueceu a senha?',
    submitLabel: 'Entrar',
    signupPrompt: 'Ainda não tem conta?',
    signupLabel: 'Criar conta',
    emailPlaceholder: 'voce@exemplo.com',
    dividerLabel: 'ou',
  },
  authSignup: {
    title: 'Crie sua conta',
    description: 'Comece agora mesmo.',
    nameLabel: 'Nome',
    namePlaceholder: 'Informe seu nome',
    emailLabel: 'E-mail',
    passwordLabel: 'Senha',
    passwordPlaceholder: 'Crie uma senha',
    passwordHint: 'Use pelo menos 8 caracteres.',
    confirmationLabel: 'Confirme a senha',
    confirmationPlaceholder: 'Repita a senha',
    submitLabel: 'Criar conta',
    signinPrompt: 'Já tem conta?',
    signinLabel: 'Entrar',
    emailPlaceholder: 'voce@exemplo.com',
    dividerLabel: 'ou',
  },
  authVerifyCode: {
    title: 'Confira seu e-mail',
    description: 'Enviamos um código de verificação para o seu e-mail.',
    codeLabel: 'Código de verificação',
    submitLabel: 'Verificar',
    resendPrompt: 'Não recebeu o código?',
    resendLabel: 'Reenviar código',
    backLabel: 'Voltar para o login',
    resendCountdownLabel: (seconds: number) => `Reenviar em ${seconds}s`,
  },
  badge: {
    dismissLabel: 'Remover',
  },
  barList: {
    showMoreLabel: 'Mostrar mais',
    showLessLabel: 'Mostrar menos',
  },
  breadcrumb: {
    label: 'Trilha de navegação',
  },
  breadcrumbEllipsis: {
    srLabel: 'Mais',
  },
  calendar: {
    prevLabel: 'Mês anterior',
    nextLabel: 'Próximo mês',
    viewLabel: 'Escolher mês e ano',
    prevYearLabel: 'Ano anterior',
    nextYearLabel: 'Próximo ano',
    prevYearsLabel: 'Anos anteriores',
    nextYearsLabel: 'Próximos anos',
    inputPlaceholder: 'DD/MM/AAAA',
    todayLabel: 'Hoje',
  },
  codeBlock: {
    copyLabel: 'Copiar',
    copiedLabel: 'Copiado',
  },
  colorPicker: {
    saturationLabel: 'Saturação e brilho',
    hueLabel: 'Matiz',
    alphaLabel: 'Opacidade',
    hexLabel: 'Cor em hexadecimal',
    eyeDropperLabel: 'Escolher cor na tela',
    swatchLabel: 'Amostra de cor',
  },
  combobox: {
    emptyText: 'Nenhum resultado encontrado.',
  },
  commandDialog: {
    title: 'Menu de comandos',
    description: 'Busque um comando para executar.',
  },
  commandInput: {
    placeholder: 'Digite um comando ou busque…',
    label: 'Buscar comandos',
  },
  commandList: {
    label: 'Comandos',
  },
  componentPreview: {
    previewLabel: 'Prévia',
    codeLabel: 'Código',
  },
  confusionMatrix: {
    predictedLabel: 'Previsto',
    actualLabel: 'Real',
  },
  cookieConsent: {
    title: 'Usamos cookies',
    acceptLabel: 'Aceitar todos',
    rejectLabel: 'Recusar todos',
  },
  dataTable: {
    emptyTitle: 'Nenhum resultado encontrado',
    emptyDescription: 'Tente ajustar a busca ou os filtros.',
    selectAllLabel: 'Selecionar todas as linhas',
    clearSelectionLabel: 'Limpar seleção',
    selectRowLabel: 'Selecionar linha',
    sortLabel: (header: string) => `Ordenar por ${header}`,
    selectedLabel: (count: number) => (count === 1 ? '1 selecionado' : `${count} selecionados`),
  },
  datePicker: {
    placeholder: 'Escolha uma data',
    clearLabel: 'Limpar',
    todayLabel: 'Hoje',
    applyLabel: 'Aplicar',
    cancelLabel: 'Cancelar',
  },
  timePicker: {
    clearLabel: 'Limpar',
    openLabel: 'Escolher horário',
  },
  dateRangePicker: {
    placeholder: 'Escolha um período',
    clearLabel: 'Limpar',
    applyLabel: 'Aplicar',
    cancelLabel: 'Cancelar',
  },
  dialogContent: {
    closeLabel: 'Fechar',
  },
  errorPage: {
    code: 'Erro 404',
    title: 'Não encontramos esta página',
    backLabel: 'Voltar',
    homeLabel: 'Ir para o início',
    description: 'A página que você procura não existe ou mudou de endereço.',
  },
  eventCalendarCompactView: {
    emptyLabel: 'Nenhum evento neste dia',
  },
  eventCalendarHeader: {
    weekLabel: 'Semana',
    todayLabel: 'Hoje',
    addEventLabel: 'Adicionar evento',
    searchLabel: 'Buscar eventos',
    monthViewLabel: 'Visão do mês',
    weekViewLabel: 'Visão da semana',
    dayViewLabel: 'Visão do dia',
    viewLabel: 'Visão do calendário',
    prevLabel: 'Anterior',
    nextLabel: 'Próximo',
  },
  eventCalendarMonthView: {
    moreLabel: 'mais',
  },
  eventDetailsPanel: {
    aboutLabel: 'Sobre este evento',
    editLabel: 'Editar evento',
    deleteLabel: 'Excluir evento',
    duplicateLabel: 'Duplicar evento',
    addGuestLabel: 'Adicionar convidado',
    emptyLabel: 'Selecione um evento para ver os detalhes',
    guestsLabel: 'convidados',
    yesLabel: 'sim',
    awaitingLabel: 'aguardando',
    noLabel: 'não',
  },
  fileInput: {
    dropLabel: 'Arraste e solte arquivos aqui, ou',
    removeLabel: 'Remover',
    maxSizeLabel: 'Máx.',
    oversizeLabel: 'O arquivo passa do tamanho máximo',
    browseLabel: 'procure',
  },
  filterBar: {
    clearLabel: 'Limpar tudo',
    removeLabel: 'Remover filtro',
  },
  filterBarRow: {
    fieldPlaceholder: 'Campo',
    operatorPlaceholder: 'Operador',
    valuePlaceholder: 'Valor',
    fieldLabel: 'Campo do filtro',
    operatorLabel: 'Operador do filtro',
    valueLabel: 'Valor do filtro',
    removeLabel: 'Remover filtro',
  },
  filterBarSearch: {
    placeholder: 'Buscar',
    label: 'Buscar',
  },
  input: {
    clearLabel: 'Limpar',
  },
  navbarTrigger: {
    label: 'Abrir ou fechar o menu',
  },
  numberField: {
    decrementLabel: 'Diminuir',
    incrementLabel: 'Aumentar',
  },
  pagePager: {
    prevLabel: 'Anterior',
    nextLabel: 'Próxima',
    label: 'Paginação',
  },
  pagination: {
    prevLabel: 'Anterior',
    nextLabel: 'Próxima',
    label: 'Paginação',
  },
  paginationFirst: {
    label: 'Primeira página',
  },
  paginationLast: {
    label: 'Última página',
  },
  paginationNext: {
    label: 'Próxima',
  },
  paginationPrev: {
    label: 'Anterior',
  },
  pricingSection: {
    monthlyLabel: 'Mensal',
    yearlyLabel: 'Anual',
    monthlySuffix: 'por mês',
    yearlySuffix: 'por ano',
    billingLabel: 'Período de cobrança',
    featuresLabel: 'Inclui',
  },
  productPreview: {
    title: 'Visão geral',
    period: 'Últimos 30 dias',
    listTitle: 'Principais contas',
    label: 'Prévia do produto',
  },
  rangeCalendar: {
    prevLabel: 'Mês anterior',
    nextLabel: 'Próximo mês',
  },
  settingsSaveBar: {
    message: 'Você tem alterações não salvas',
    discardLabel: 'Descartar',
    saveLabel: 'Salvar alterações',
  },
  sheetContent: {
    closeLabel: 'Fechar',
    title: 'Painel',
    description: 'Painel lateral',
  },
  sidebar: {
    mobileLabel: 'Navegação',
    label: 'Barra lateral',
  },
  sidebarTrigger: {
    label: 'Abrir ou fechar a barra lateral',
  },
  siteFooter: {
    navigationLabel: 'Rodapé',
  },
  spinner: {
    label: 'Carregando',
  },
  tableHead: {
    sortLabel: 'Ordenar',
  },
  tableOfContents: {
    label: 'Nesta página',
  },
  tagsInput: {
    deleteLabel: 'Remover',
  },
  testimonialSection: {
    ratingLabel: (rating: number) => `Nota ${rating} de 5`,
  },
  toaster: {
    closeLabel: 'Fechar',
  },
  tooltipContent: {
    dismissLabel: 'Dispensar',
  },
  uptimeCard: {
    startLabel: '90 dias atrás',
    endLabel: 'Hoje',
  },
};
