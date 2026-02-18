<?php
/**
 * Base Model Class
 * VehiCare Service Management System
 * 
 * Provides common database operations for all models
 */

abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';
    protected $timestamps = true;
    protected $fillable = [];
    protected $hidden = ['password'];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$id]);
            return $this->hideFields($stmt->fetch());
        } catch (Exception $e) {
            log_event('ERROR', "Error finding record: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find all records with optional conditions
     */
    public function findAll($where = '', $params = [], $orderBy = '', $limit = '') {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($where)) {
                $sql .= " WHERE " . $where;
            }
            
            if (!empty($orderBy)) {
                $sql .= " ORDER BY " . $orderBy;
            }
            
            if (!empty($limit)) {
                $sql .= " LIMIT " . $limit;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll();
            return array_map([$this, 'hideFields'], $results);
        } catch (Exception $e) {
            log_event('ERROR', "Error finding records: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        try {
            // Filter only fillable fields
            $fillableData = $this->filterFillable($data);
            
            // Add timestamps if enabled
            if ($this->timestamps) {
                $fillableData['created_date'] = date('Y-m-d H:i:s');
                $fillableData['updated_date'] = date('Y-m-d H:i:s');
            }
            
            $columns = implode(', ', array_keys($fillableData));
            $placeholders = ':' . implode(', :', array_keys($fillableData));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($fillableData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            if ($stmt->execute()) {
                $id = $this->pdo->lastInsertId();
                log_event('INFO', "Created record in {$this->table} with ID: $id");
                return $this->find($id);
            }
            
            return false;
        } catch (Exception $e) {
            log_event('ERROR', "Error creating record: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        try {
            // Get existing record for audit trail
            $oldRecord = $this->find($id);
            if (!$oldRecord) {
                return false;
            }
            
            // Filter only fillable fields
            $fillableData = $this->filterFillable($data);
            
            // Add updated timestamp
            if ($this->timestamps) {
                $fillableData['updated_date'] = date('Y-m-d H:i:s');
            }
            
            $setParts = [];
            foreach (array_keys($fillableData) as $column) {
                $setParts[] = "{$column} = :{$column}";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE {$this->primaryKey} = :id";
            $stmt = $this->pdo->prepare($sql);
            
            // Bind values
            foreach ($fillableData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':id', $id);
            
            if ($stmt->execute()) {
                log_event('INFO', "Updated record in {$this->table} with ID: $id");
                return $this->find($id);
            }
            
            return false;
        } catch (Exception $e) {
            log_event('ERROR', "Error updating record: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        try {
            // Get existing record for audit trail
            $record = $this->find($id);
            if (!$record) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
            
            if ($stmt->execute([$id])) {
                log_event('INFO', "Deleted record from {$this->table} with ID: $id");
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            log_event('ERROR', "Error deleting record: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count records
     */
    public function count($where = '', $params = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            
            // Handle array-style where conditions
            if (is_array($where)) {
                $conditions = [];
                $params = [];
                foreach ($where as $key => $value) {
                    $conditions[] = "{$key} = ?";
                    $params[] = $value;
                }
                if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(' AND ', $conditions);
                }
            } elseif (!empty($where)) {
                $sql .= " WHERE " . $where;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            log_event('ERROR', "Error counting records: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Execute custom query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            log_event('ERROR', "Error executing query: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction  
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Protected helper methods
     */
    
    protected function filterFillable($data) {
        $fillable = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $fillable[$key] = $value;
            }
        }
        return $fillable;
    }
    
    protected function hideFields($record) {
        if (!$record) return $record;
        
        foreach ($this->hidden as $field) {
            if (isset($record[$field])) {
                unset($record[$field]);
            }
        }
        return $record;
    }
    
    /**
     * Search functionality
     */
    public function search($term, $fields = [], $limit = 10) {
        try {
            if (empty($fields)) {
                return [];
            }
            
            $whereParts = [];
            $params = [];
            
            foreach ($fields as $field) {
                $whereParts[] = "{$field} LIKE ?";
                $params[] = "%{$term}%";
            }
            
            $where = '(' . implode(' OR ', $whereParts) . ')';
            
            return $this->findAll($where, $params, '', $limit);
        } catch (Exception $e) {
            log_event('ERROR', "Error searching records: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Pagination
     */
    public function paginate($page = 1, $perPage = 10, $where = '', $params = [], $orderBy = '') {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = $this->count($where, $params);
        
        // Get records for current page
        $records = $this->findAll($where, $params, $orderBy, "{$offset}, {$perPage}");
        
        return [
            'data' => $records,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'has_more' => ($page * $perPage) < $total
        ];
    }
}
?>