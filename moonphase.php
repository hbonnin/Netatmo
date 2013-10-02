<?php

    class moon {

        private $date;

        /*
         * set a property, only date at this time
         * @param prop
         * @param val
         * @return bool
         */
        public function set($prop, $val) {
            $r = false;
            switch ($prop) {
                case "date":
                    $r = $this->date = is_numeric($val) ? $val : strtotime($val);
            }
            return (bool) $r;
        }

        /*
         * calc moon phase percentage for a date
         * @return int
         */
        public function get_phase_percent() {
            $r = false;
            if ($this->date) {
                $x = ($this->date - 603240) / 2551392;
                $x -= (int) $x;
                $r = round($x * 100);
            }
            return $r;
        }

        /*
         * calc moon visibility
         * @return int
         */
        public function get_visibility() {
            $r = false;
            if ($this->date) $r = (50 - abs(50 - $this->get_phase_percent())) * 2;
            return $r;
        }

        /*
         * calc when new phase start
         * @param ts if true return unix timestamp
         * @return string
         */
        public function get_next_newmoon($ts = false) {
            $r = false;
            if ($this->date) $r = $this->date + 2551392 - ($this->date - 603240) % 2551392;
            return $ts ? $r : date("Y-m-d", $r);
        }

        /*
         * calc next full moon
         * @param ts if true return unix timestamp
         * @return string
         */
        public function get_next_fullmoon($ts = false) {
            $r = false;
            if ($this->date) $r = $this->get_next_newmoon(true) + 1275696;
            return $ts ? $r : date("Y-m-d H:i", $r);
        }

        /*
         * get moon phase name
         * @return string
         */
        public function get_phase_name() {
            $r = false;
            if ($this->date) {
                $x = round($this->get_phase_percent() * 0.1) * 10;
                if ($x == 0 || $x == 100) $r = "New";
                elseif ($x > 0 && $x < 25) $r = "Waxing crescent";
                elseif ($x == 25) $r = "First quarter";
                elseif ($x > 25 && $x < 50) $r = "Waxing gibbous";
                elseif ($x == 50) $r = "Full";
                elseif ($x > 50 && $x < 75) $r = "Waning gibbous";
                elseif ($x == 75) $r = "Last quarter";
                elseif ($x > 75) $r = "Waning crescent";
            }
            return $r;
        }

    }

?>
