<?php
class ProblemHelper {
    public static function getProblemTypeName($type) {
        global $TYPE_PROBLEM;
        
        if (isset($TYPE_PROBLEM[$type])) {
            return $TYPE_PROBLEM[$type]['name'];
        }
        
        return str_replace('_', ' ', $type);
    }

    public static function getProblemTypeIcon($type) {
        global $TYPE_PROBLEM;
        
        if (isset($TYPE_PROBLEM[$type])) {
            return $TYPE_PROBLEM[$type]['icon'];
        }
        
        return 'bx-code-alt';
    }

    public static function getProblemTypeDescription($type) {
        global $TYPE_PROBLEM;
        
        if (isset($TYPE_PROBLEM[$type])) {
            return $TYPE_PROBLEM[$type]['description'];
        }
        
        return '';
    }

    public static function formatProblemTypeTags($problemTypes, $maxDisplay = 4) {
        if (empty($problemTypes)) {
            return '';
        }
        
        if (is_string($problemTypes)) {
            $problemTypes = json_decode($problemTypes, true);
        }
        
        if (!is_array($problemTypes)) {
            return '';
        }
        
        $html = '';
        $displayTypes = array_slice($problemTypes, 0, $maxDisplay);
        
        foreach ($displayTypes as $type) {
            $name = self::getProblemTypeName($type);
            $icon = self::getProblemTypeIcon($type);
            $description = self::getProblemTypeDescription($type);
            
            $html .= sprintf(
                '<span class="tag" title="%s"><i class="bx %s"></i>%s</span>',
                htmlspecialchars($description),
                htmlspecialchars($icon),
                htmlspecialchars($name)
            );
        }
        
        return $html;
    }

    public static function getAllProblemTypes() {
        global $TYPE_PROBLEM;
        
        $types = [];
        foreach ($TYPE_PROBLEM as $key => $value) {
            $types[$key] = $value['name'];
        }
        
        return $types;
    }

    public static function formatDifficulty($difficulty) {
        $difficulties = [
            'easy' => 'Dễ',
            'medium' => 'Trung bình',
            'hard' => 'Khó'
        ];
        
        return $difficulties[$difficulty] ?? ucfirst($difficulty);
    }

    public static function getDifficultyColor($difficulty) {
        $colors = [
            'easy' => '#00b894',
            'medium' => '#fdcb6e',
            'hard' => '#e17055'
        ];
        
        return $colors[$difficulty] ?? '#6c757d';
    }

    public static function formatAcceptanceRate($rate) {
        if ($rate === null || $rate === 0) {
            return 'N/A';
        }
        
        return number_format($rate, 1) . '%';
    }

    public static function formatSolvedCount($count) {
        if ($count < 1000) {
            return number_format($count);
        } elseif ($count < 1000000) {
            return number_format($count / 1000, 1) . 'K';
        } else {
            return number_format($count / 1000000, 1) . 'M';
        }
    }
}
?>
