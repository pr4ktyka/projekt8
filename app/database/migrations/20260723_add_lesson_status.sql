-- Migration: add status column for lessons used by Lesson.php logic
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'lessons'
      AND COLUMN_NAME = 'status'
);

SET @add_col_sql = IF(
    @col_exists = 0,
    "ALTER TABLE lessons ADD COLUMN status ENUM('draft', 'published') NOT NULL DEFAULT 'published' AFTER level_id",
    'SELECT 1'
);

PREPARE add_col_stmt FROM @add_col_sql;
EXECUTE add_col_stmt;
DEALLOCATE PREPARE add_col_stmt;

SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'lessons'
      AND INDEX_NAME = 'idx_lessons_status'
);

SET @add_idx_sql = IF(
    @idx_exists = 0,
    'CREATE INDEX idx_lessons_status ON lessons (status)',
    'SELECT 1'
);

PREPARE add_idx_stmt FROM @add_idx_sql;
EXECUTE add_idx_stmt;
DEALLOCATE PREPARE add_idx_stmt;
