const locale = 'pt-BR';

function toUtcDate(isoDate: string): Date {
    const [year, month, day] = isoDate.split('-').map(Number);

    return new Date(Date.UTC(year ?? 1970, (month ?? 1) - 1, day ?? 1));
}

function capitalize(text: string): string {
    return text.charAt(0).toLocaleUpperCase(locale) + text.slice(1);
}

export function isoWeekday(isoDate: string): number {
    return ((toUtcDate(isoDate).getUTCDay() + 6) % 7) + 1;
}

export function dayOfMonth(isoDate: string): number {
    return toUtcDate(isoDate).getUTCDate();
}

export function formatMonthTitle(month: string): string {
    return capitalize(
        new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(
            toUtcDate(`${month}-01`),
        ),
    );
}

export function formatDayTitle(isoDate: string): string {
    return capitalize(
        new Intl.DateTimeFormat(locale, { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(
            toUtcDate(isoDate),
        ),
    );
}

export function formatShortWeekday(isoDate: string): string {
    return capitalize(
        new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone: 'UTC' })
            .format(toUtcDate(isoDate))
            .replace('.', ''),
    );
}

export function formatDate(isoDate: string): string {
    return new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(toUtcDate(isoDate));
}

export function formatDayAndMonth(isoDate: string): string {
    return new Intl.DateTimeFormat(locale, { day: '2-digit', month: '2-digit', timeZone: 'UTC' }).format(
        toUtcDate(isoDate),
    );
}

export function toIsoDate(date: Date): string {
    return [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-');
}

export function fromIsoDate(isoDate: string): Date {
    const [year, month, day] = isoDate.split('-').map(Number);

    return new Date(year ?? 1970, (month ?? 1) - 1, day ?? 1);
}
