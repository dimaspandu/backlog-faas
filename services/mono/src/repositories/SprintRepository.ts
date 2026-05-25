import db from '../db';

export interface SprintFilter {
  isVisible?: boolean;
  isOpen?: boolean;
}

export class SprintRepository {
  async findSprints(filter: SprintFilter = {}) {
    const conditions: string[] = [];
    const params: any[] = [];
    if (filter.isVisible !== undefined) {
      conditions.push('is_visible = ?');
      params.push(filter.isVisible ? 1 : 0);
    }
    if (filter.isOpen !== undefined) {
      conditions.push('is_open = ?');
      params.push(filter.isOpen ? 1 : 0);
    }
    const where = conditions.length ? ('WHERE ' + conditions.join(' AND ')) : '';
    const sql = `SELECT id, token, name, description, start_at as startAt, end_at as endAt, is_visible as isVisible, is_open as isOpen, status, created_at as createdAt FROM sprints ${where} ORDER BY start_at DESC`;
    const [rows] = await db.query(sql, params);
    return rows as any[];
  }
}
