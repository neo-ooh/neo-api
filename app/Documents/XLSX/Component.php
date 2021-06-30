<?php

namespace Neo\Documents\XLSX;

abstract class Component {
    abstract public function render(Worksheet $ws);
}
