
    /**
     * Get human readable session duration
     */
    public function getSessionDurationHumanAttribute()
    {
        if (!$this->session_duration) {
            return '-';
        }
        
        $hours = floor($this->session_duration / 3600);
        $minutes = floor(($this->session_duration % 3600) / 60);
        $seconds = $this->session_duration % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
    
    /**
     * Relationship: gebruiker
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }