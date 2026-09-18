export function clockToMinutes(clock: string): number | null {
    const match = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(clock);

    return match ? Number(match[1]) * 60 + Number(match[2]) : null;
}

export function minutesToClock(minutes: number): string {
    const hours = Math.floor(minutes / 60);

    return `${String(hours).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;
}

export function formatDuration(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    if (hours === 0 && rest > 0) {
        return `${rest}min`;
    }

    return rest === 0 ? `${hours}h` : `${hours}h${String(rest).padStart(2, '0')}`;
}
