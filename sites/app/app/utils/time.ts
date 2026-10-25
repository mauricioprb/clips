export const HOUR_HEIGHT = 48;

const DEFAULT_START_HOUR = 7;
const DEFAULT_END_HOUR = 19;

export interface TimeRange {
  startMinute: number;
  endMinute: number;
}

export function clockToMinutes(clock: string | undefined): number | null {
  const match = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(clock ?? '');

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

export function visibleHours(ranges: TimeRange[]): { startHour: number; endHour: number } {
  const earliest = Math.min(
    DEFAULT_START_HOUR,
    ...ranges.map((range) => Math.floor(range.startMinute / 60)),
  );
  const latest = Math.max(
    DEFAULT_END_HOUR,
    ...ranges.map((range) => Math.ceil(range.endMinute / 60)),
  );

  return { startHour: earliest, endHour: Math.min(latest, 24) };
}

export function blockPosition(
  range: TimeRange,
  startHour: number,
): { top: number; height: number } {
  return {
    top: ((range.startMinute - startHour * 60) / 60) * HOUR_HEIGHT,
    height: ((range.endMinute - range.startMinute) / 60) * HOUR_HEIGHT,
  };
}

export function minuteAtOffset(offsetY: number, startHour: number, step = 30): number {
  const minute = startHour * 60 + (offsetY / HOUR_HEIGHT) * 60;

  return Math.min(Math.max(Math.floor(minute / step) * step, startHour * 60), 1440 - step);
}
