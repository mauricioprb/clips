export type EventColor =
  | 'primary'
  | 'neutral'
  | 'blue'
  | 'sky'
  | 'cyan'
  | 'teal'
  | 'green'
  | 'yellow'
  | 'amber'
  | 'orange'
  | 'red'
  | 'violet'
  | 'purple'
  | 'indigo';

export interface CalendarEvent {
  id: string | number;
  title: string;
  start: Date | string;
  end?: Date | string;
  allDay?: boolean;
  color?: EventColor;
}
