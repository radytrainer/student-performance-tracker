<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SchoolIsolation
{
    /**
     * Get the current user's school ID
     */
    protected function getCurrentSchoolId()
    {
        return auth()->user()->school_id;
    }

    /**
     * Apply school isolation to a query
     */
    protected function applySchoolIsolation(Builder $query)
    {
        $schoolId = $this->getCurrentSchoolId();
        
        if ($schoolId) {
            return $query->where('school_id', $schoolId);
        }
        
        // If no school ID, return empty result for security
        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply school isolation to users query
     */
    protected function applyUserSchoolIsolation(Builder $query)
    {
        return $query->forSchool($this->getCurrentSchoolId());
    }

    /**
     * Apply school isolation to classes query
     */
    protected function applyClassSchoolIsolation(Builder $query)
    {
        return $query->forSchool($this->getCurrentSchoolId());
    }

    /**
     * Check if user can access a model by school
     */
    protected function canAccessBySchool($model)
    {
        if (!$model) {
            return false;
        }

        // If model has school_id property
        if (isset($model->school_id)) {
            return $model->school_id === $this->getCurrentSchoolId();
        }

        // If model is a User, check their school_id
        if ($model instanceof \App\Models\User) {
            return $model->school_id === $this->getCurrentSchoolId();
        }

        return false;
    }

    /**
     * Ensure data belongs to current user's school
     */
    protected function ensureSchoolData($data)
    {
        if (is_array($data)) {
            $data['school_id'] = $this->getCurrentSchoolId();
        } elseif (is_object($data)) {
            $data->school_id = $this->getCurrentSchoolId();
        }

        return $data;
    }
}
