<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class LibraryData extends ActiveRecord {
    public static function tableName() {
        return 'library_data'; // Ganti dengan nama tabel yang sesuai
    }

    public function rules() {
        return [
            [['population', 'libraries', 'collections', 'library_staff', 'daily_visitors', 'trained_libraries', 'community_participation', 'library_members'], 'required'],
            [['population', 'libraries', 'collections', 'library_staff', 'daily_visitors', 'trained_libraries', 'community_participation', 'library_members'], 'integer'],
        ];
    }

    public function calculateIPLM() {
        $uplm1 = $this->libraries / $this->population;
        $uplm2 = $this->collections / $this->population;
        $uplm3 = ($this->library_staff / $this->population) * 0.0004;
        $uplm4 = ($this->daily_visitors / $this->population) * 0.02;
        $uplm5 = ($this->trained_libraries / $this->libraries) * 0.2235;
        $uplm6 = ($this->community_participation / $this->population) * 0.02;
        $uplm7 = ($this->library_members / $this->population) * 0.02;

        return ($uplm1 + $uplm2 + $uplm3 + $uplm4 + $uplm5 + $uplm6 + $uplm7) / 7;
    }
}
