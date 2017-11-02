<?php

namespace Models;

class ScheduledMovement extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'movimentosProgramados';

    public function movement(){
        return $this->hasOne(\Models\Movement::class, 'id', 'movimento');
    }

}
