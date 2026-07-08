# wpvibes/review-reminder

A lightweight, shared admin reminder library for requesting WordPress.org reviews across the WPVibes plugin portfolio.

## Why this exists

We have 9+ plugins and want consistent review prompt logic, copy, and styling across all of them. Building it once means we tune it once. No third-party SDK, no telemetry, no surprise UI.

## What it does

- Shows a WP-native admin reminder asking for a review at the right moment
- Triggers based on time, usage, both, or either — configurable per plugin
- Four user actions: Rate now / Already did / Remind in 30 days / Never show again
- Global cooldown across plugins so a user with three of our plugins isn't asked three times in one week
- Single-option storage per plugin, autoload disabled
- AJAX dismissal with nonce protection, vanilla JS, no jQuery

## Requirements

- PHP 7.4+
- WordPress 5.5+

## Installation

```bash
composer require wpvibes/review-reminder
```

If shipping inside a public WP.org plugin, prefix the namespace with [Strauss](https://github.com/BrianHenryIE/strauss) at build time to avoid collisions when a user has multiple WPVibes plugins installed:

```json
{
    "extra": {
        "strauss": {
            "target_directory": "vendor-prefixed",
            "namespace_prefix": "WPVibes\\FormVibes\\Vendor\\",
            "classmap_prefix": "WPVibes_FormVibes_Vendor_"
        }
    }
}
```

## Quick integration

In your plugin's main file, after Composer's autoloader is required:

```php
use WPVibes\ReviewReminder\ReviewReminder;

add_action( 'plugins_loaded', function () {
    ReviewReminder::register( [
        'plugin_slug' => 'form-vibes',
        'plugin_name' => 'Form Vibes',
        'plugin_file' => __FILE__,
        'text_domain' => 'form-vibes',

        'triggers' => [
            'time'  => 7 * DAY_IN_SECONDS,
            'usage' => [
                'option_key' => 'submissions_logged',
                'threshold'  => 25,
            ],
        ],
        'trigger_logic' => 'AND',

        'screens'    => [ 'dashboard', 'plugins', 'form-vibes_page_*' ],
        'capability' => 'manage_options',
        'icon_url'   => plugins_url( 'assets/icon.png', __FILE__ ),
    ] );
} );
```

Then increment usage at your value moments:

```php
ReviewReminder::increment( 'form-vibes', 'submissions_logged' );
```

## Configuration reference

| Key | Required | Type | Description |
|---|---|---|---|
| `plugin_slug` | yes | string | WP.org plugin slug, used in the review URL |
| `plugin_name` | yes | string | Display name shown in the reminder |
| `plugin_file` | yes | string | Pass `__FILE__` from your main plugin file (used for activation hook) |
| `text_domain` | no | string | Defaults to `plugin_slug` |
| `triggers.time` | conditional | int | Seconds after install before reminder can show. Set to `null` or omit to disable |
| `triggers.usage.option_key` | conditional | string | Counter key. Pass this same string to `increment()` |
| `triggers.usage.threshold` | conditional | int | Counter must reach this value |
| `trigger_logic` | no | string | `AND` (default), `OR`, `TIME_ONLY`, `USAGE_ONLY` |
| `screens` | no | string[] | Admin screen IDs. Trailing `*` is a wildcard. Defaults to `[dashboard, plugins]` |
| `capability` | no | string | Defaults to `manage_options` |
| `icon_url` | no | string | Optional 56×56 plugin icon shown in the reminder |

At least one of `triggers.time` or `triggers.usage` must be configured.

## Trigger logic

| Logic | Behavior |
|---|---|
| `AND` | Both time AND usage must be met. Falls back to whichever is configured if only one is. |
| `OR` | Either time OR usage being met is enough |
| `TIME_ONLY` | Ignore usage even if configured |
| `USAGE_ONLY` | Ignore time even if configured |

## Recommended settings per plugin type

| Plugin | Logic | Time | Usage | Counter |
|---|---|---|---|---|
| Form Vibes | AND | 7 days | 25 submissions | `submissions_logged` |
| Addon Elements | AND | 7 days | 5 widgets used | `widgets_rendered` |
| WP Mail Log | TIME_ONLY | 14 days | — | — |
| Frontend Product Editor | USAGE_ONLY | — | 10 edits | `products_edited` |
| Quick View Popup | OR | 14 days | 50 popup opens | `popups_opened` |

## State machine

```
                    ┌─────────────┐
                    │   pending   │ ◄──── snooze expires (30d)
                    └──────┬──────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
   ┌─────────┐       ┌──────────┐       ┌────────────┐
   │  rated  │       │ snoozed  │       │ dismissed  │
   │(terminal│       │  (30d)   │       │ (terminal) │
   └─────────┘       └────┬─────┘       └────────────┘
                          │
                          └──► back to pending after 30 days
```

`rated` and `dismissed` are permanent. `snoozed` automatically returns to `pending` after 30 days.

## Global cooldown

When any registered plugin shows its reminder, a 14-day transient lock prevents other WPVibes plugins from showing theirs. This protects users who have multiple WPVibes plugins installed from feeling spammed.

## Testing

Reset state during development:

```php
\WPVibes\ReviewReminder\ReviewReminder::reset( 'form-vibes' );
```

Or via WP-CLI:

```bash
wp option delete wpvibes_review_reminder_form-vibes
wp transient delete wpvibes_review_reminder_global_cooldown
```

## Storage

- Per-plugin state: `wpvibes_review_reminder_{slug}` (autoload off)
- Global cooldown: `wpvibes_review_reminder_global_cooldown` (transient, 14 days)

## License

GPL-2.0-or-later
