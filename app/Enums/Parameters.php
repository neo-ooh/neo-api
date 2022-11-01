<?php

namespace Neo\Enums;

enum Parameters: string implements ParametersEnum {
    case ToS = 'Tos';
    case WelcomeTextEn = 'WELCOME_TEXT_EN';
    case WelcomeTextFr = 'WELCOME_TEXT_FR';

    public function defaultValue(): mixed {
        return match ($this) {
            self::ToS                                => null,
            self::WelcomeTextEn, self::WelcomeTextFr => "",
        };
    }

    public function format(): string {
        return match ($this) {
            self::ToS                                => "file:pdf",
            self::WelcomeTextEn, self::WelcomeTextFr => "text",
        };
    }
}
