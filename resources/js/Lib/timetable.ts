export const HOUR_HEIGHT = 48;

const DEFAULT_START_HOUR = 7;
const DEFAULT_END_HOUR = 19;

export interface TimeRange {
    startMinute: number;
    endMinute: number;
}

export function visibleHours(ranges: TimeRange[]): { startHour: number; endHour: number } {
    const earliest = Math.min(DEFAULT_START_HOUR, ...ranges.map((range) => Math.floor(range.startMinute / 60)));
    const latest = Math.max(DEFAULT_END_HOUR, ...ranges.map((range) => Math.ceil(range.endMinute / 60)));

    return { startHour: earliest, endHour: Math.min(latest, 24) };
}

export function blockPosition(range: TimeRange, startHour: number): { top: number; height: number } {
    return {
        top: ((range.startMinute - startHour * 60) / 60) * HOUR_HEIGHT,
        height: ((range.endMinute - range.startMinute) / 60) * HOUR_HEIGHT,
    };
}

export function minuteAtOffset(offsetY: number, startHour: number, step = 30): number {
    const minute = startHour * 60 + (offsetY / HOUR_HEIGHT) * 60;

    return Math.min(Math.max(Math.floor(minute / step) * step, startHour * 60), 1440 - step);
}
