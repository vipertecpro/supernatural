<?php

namespace App\Enums;

enum OnboardingStep: string
{
    case Introduction = 'introduction';
    case UniverseInterests = 'universe_interests';
    case ViewingProgress = 'viewing_progress';
    case SpoilerPreferences = 'spoiler_preferences';
    case ViewingOrder = 'viewing_order';
    case PrivacyDefaults = 'privacy_defaults';
    case Review = 'review';
    case Completed = 'completed';

    /** @return list<self> */
    public static function workflow(): array
    {
        return [
            self::Introduction,
            self::UniverseInterests,
            self::ViewingProgress,
            self::SpoilerPreferences,
            self::ViewingOrder,
            self::PrivacyDefaults,
            self::Review,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Introduction => 'Introduction',
            self::UniverseInterests => 'Universe interests',
            self::ViewingProgress => 'Viewing progress',
            self::SpoilerPreferences => 'Spoiler preferences',
            self::ViewingOrder => 'Viewing order',
            self::PrivacyDefaults => 'Privacy defaults',
            self::Review => 'Review',
            self::Completed => 'Completed',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Introduction => 'onboarding.introduction',
            self::UniverseInterests => 'onboarding.interests.edit',
            self::ViewingProgress => 'onboarding.progress.edit',
            self::SpoilerPreferences => 'onboarding.spoilers.edit',
            self::ViewingOrder => 'onboarding.viewing-order.edit',
            self::PrivacyDefaults => 'onboarding.privacy.edit',
            self::Review => 'onboarding.review',
            self::Completed => 'dashboard',
        };
    }

    public function position(): int
    {
        return match ($this) {
            self::Introduction => 0,
            self::UniverseInterests => 1,
            self::ViewingProgress => 2,
            self::SpoilerPreferences => 3,
            self::ViewingOrder => 4,
            self::PrivacyDefaults => 5,
            self::Review => 6,
            self::Completed => 7,
        };
    }

    public function next(): self
    {
        return match ($this) {
            self::Introduction => self::UniverseInterests,
            self::UniverseInterests => self::ViewingProgress,
            self::ViewingProgress => self::SpoilerPreferences,
            self::SpoilerPreferences => self::ViewingOrder,
            self::ViewingOrder => self::PrivacyDefaults,
            self::PrivacyDefaults => self::Review,
            self::Review, self::Completed => self::Completed,
        };
    }

    public function previous(): ?self
    {
        return match ($this) {
            self::Introduction => null,
            self::UniverseInterests => self::Introduction,
            self::ViewingProgress => self::UniverseInterests,
            self::SpoilerPreferences => self::ViewingProgress,
            self::ViewingOrder => self::SpoilerPreferences,
            self::PrivacyDefaults => self::ViewingOrder,
            self::Review => self::PrivacyDefaults,
            self::Completed => self::Review,
        };
    }
}
