<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\control;
/**
 * 
 * @see http://fortawesome.github.io/Font-Awesome/cheatsheet/
 * @see http://fortawesome.github.io/Font-Awesome/cheatsheet/
 */
class IconType {
	const ICON_GLASS = 'fa fa-glass';
	const ICON_MUSIC = 'fa fa-music';
	const ICON_SEARCH = 'fa fa-search';
	const ICON_ENVELOPE_O = 'fa fa-envelope-o';
	const ICON_HEART = 'fa fa-heart';
	const ICON_STAR = 'fa fa-star';
	const ICON_STAR_O = 'fa fa-star-o';
	const ICON_USER = 'fa fa-user';
	const ICON_FILM = 'fa fa-film';
	const ICON_TH_LARGE = 'fa fa-th-large';
	const ICON_TH = 'fa fa-th';
	const ICON_TH_LIST = 'fa fa-th-list';
	const ICON_CHECK = 'fa fa-check';
	const ICON_REMOVE = 'fa fa-remove';
	const ICON_CLOSE = 'fa fa-close';
	const ICON_TIMES = 'fa fa-times';
	const ICON_SEARCH_PLUS = 'fa fa-search-plus';
	const ICON_SEARCH_MINUS = 'fa fa-search-minus';
	const ICON_POWER_OFF = 'fa fa-power-off';
	const ICON_SIGNAL = 'fa fa-signal';
	const ICON_GEAR = 'fa fa-gear';
	const ICON_COG = 'fa fa-cog';
	const ICON_TRASH_O = 'fa fa-trash-o';
	const ICON_HOME = 'fa fa-home';
	const ICON_FILE_O = 'fa fa-file-o';
	const ICON_CLOCK_O = 'fa fa-clock-o';
	const ICON_ROAD = 'fa fa-road';
	const ICON_DOWNLOAD = 'fa fa-download';
	const ICON_ARROW_CIRCLE_O_DOWN = 'fa fa-arrow-circle-o-down';
	const ICON_ARROW_CIRCLE_O_UP = 'fa fa-arrow-circle-o-up';
	const ICON_INBOX = 'fa fa-inbox';
	const ICON_PLAY_CIRCLE_O = 'fa fa-play-circle-o';
	const ICON_ROTATE_RIGHT = 'fa fa-rotate-right';
	const ICON_REPEAT = 'fa fa-repeat';
	const ICON_REFRESH = 'fa fa-refresh';
	const ICON_LIST_ALT = 'fa fa-list-alt';
	const ICON_LOCK = 'fa fa-lock';
	const ICON_FLAG = 'fa fa-flag';
	const ICON_HEADPHONES = 'fa fa-headphones';
	const ICON_VOLUME_OFF = 'fa fa-volume-off';
	const ICON_VOLUME_DOWN = 'fa fa-volume-down';
	const ICON_VOLUME_UP = 'fa fa-volume-up';
	const ICON_QRCODE = 'fa fa-qrcode';
	const ICON_BARCODE = 'fa fa-barcode';
	const ICON_TAG = 'fa fa-tag';
	const ICON_TAGS = 'fa fa-tags';
	const ICON_BOOK = 'fa fa-book';
	const ICON_BOOKMARK = 'fa fa-bookmark';
	const ICON_PRINT = 'fa fa-print';
	const ICON_CAMERA = 'fa fa-camera';
	const ICON_FONT = 'fa fa-font';
	const ICON_BOLD = 'fa fa-bold';
	const ICON_ITALIC = 'fa fa-italic';
	const ICON_TEXT_HEIGHT = 'fa fa-text-height';
	const ICON_TEXT_WIDTH = 'fa fa-text-width';
	const ICON_ALIGN_LEFT = 'fa fa-align-left';
	const ICON_ALIGN_CENTER = 'fa fa-align-center';
	const ICON_ALIGN_RIGHT = 'fa fa-align-right';
	const ICON_ALIGN_JUSTIFY = 'fa fa-align-justify';
	const ICON_LIST = 'fa fa-list';
	const ICON_DEDENT = 'fa fa-dedent';
	const ICON_OUTDENT = 'fa fa-outdent';
	const ICON_INDENT = 'fa fa-indent';
	const ICON_VIDEO_CAMERA = 'fa fa-video-camera';
	const ICON_PHOTO = 'fa fa-photo';
	const ICON_IMAGE = 'fa fa-image';
	const ICON_PICTURE_O = 'fa fa-picture-o';
	const ICON_PENCIL = 'fa fa-pencil';
	const ICON_MAP_MARKER = 'fa fa-map-marker';
	const ICON_ADJUST = 'fa fa-adjust';
	const ICON_TINT = 'fa fa-tint';
	const ICON_EDIT = 'fa fa-edit';
	const ICON_PENCIL_SQUARE_O = 'fa fa-pencil-square-o';
	const ICON_SHARE_SQUARE_O = 'fa fa-share-square-o';
	const ICON_CHECK_SQUARE_O = 'fa fa-check-square-o';
	const ICON_ARROWS = 'fa fa-arrows';
	const ICON_STEP_BACKWARD = 'fa fa-step-backward';
	const ICON_FAST_BACKWARD = 'fa fa-fast-backward';
	const ICON_BACKWARD = 'fa fa-backward';
	const ICON_PLAY = 'fa fa-play';
	const ICON_PAUSE = 'fa fa-pause';
	const ICON_STOP = 'fa fa-stop';
	const ICON_FORWARD = 'fa fa-forward';
	const ICON_FAST_FORWARD = 'fa fa-fast-forward';
	const ICON_STEP_FORWARD = 'fa fa-step-forward';
	const ICON_EJECT = 'fa fa-eject';
	const ICON_CHEVRON_LEFT = 'fa fa-chevron-left';
	const ICON_CHEVRON_RIGHT = 'fa fa-chevron-right';
	const ICON_PLUS_CIRCLE = 'fa fa-plus-circle';
	const ICON_MINUS_CIRCLE = 'fa fa-minus-circle';
	const ICON_TIMES_CIRCLE = 'fa fa-times-circle';
	const ICON_CHECK_CIRCLE = 'fa fa-check-circle';
	const ICON_QUESTION_CIRCLE = 'fa fa-question-circle';
	const ICON_INFO_CIRCLE = 'fa fa-info-circle';
	const ICON_CROSSHAIRS = 'fa fa-crosshairs';
	const ICON_TIMES_CIRCLE_O = 'fa fa-times-circle-o';
	const ICON_CHECK_CIRCLE_O = 'fa fa-check-circle-o';
	const ICON_BAN = 'fa fa-ban';
	const ICON_ARROW_LEFT = 'fa fa-arrow-left';
	const ICON_ARROW_RIGHT = 'fa fa-arrow-right';
	const ICON_ARROW_UP = 'fa fa-arrow-up';
	const ICON_ARROW_DOWN = 'fa fa-arrow-down';
	const ICON_MAIL_FORWARD = 'fa fa-mail-forward';
	const ICON_SHARE = 'fa fa-share';
	const ICON_EXPAND = 'fa fa-expand';
	const ICON_COMPRESS = 'fa fa-compress';
	const ICON_PLUS = 'fa fa-plus';
	const ICON_MINUS = 'fa fa-minus';
	const ICON_ASTERISK = 'fa fa-asterisk';
	const ICON_EXCLAMATION_CIRCLE = 'fa fa-exclamation-circle';
	const ICON_GIFT = 'fa fa-gift';
	const ICON_LEAF = 'fa fa-leaf';
	const ICON_FIRE = 'fa fa-fire';
	const ICON_EYE = 'fa fa-eye';
	const ICON_EYE_SLASH = 'fa fa-eye-slash';
	const ICON_WARNING = 'fa fa-warning';
	const ICON_EXCLAMATION_TRIANGLE = 'fa fa-exclamation-triangle';
	const ICON_PLANE = 'fa fa-plane';
	const ICON_CALENDAR = 'fa fa-calendar';
	const ICON_RANDOM = 'fa fa-random';
	const ICON_COMMENT = 'fa fa-comment';
	const ICON_MAGNET = 'fa fa-magnet';
	const ICON_CHEVRON_UP = 'fa fa-chevron-up';
	const ICON_CHEVRON_DOWN = 'fa fa-chevron-down';
	const ICON_RETWEET = 'fa fa-retweet';
	const ICON_SHOPPING_CART = 'fa fa-shopping-cart';
	const ICON_FOLDER = 'fa fa-folder';
	const ICON_FOLDER_OPEN = 'fa fa-folder-open';
	const ICON_ARROWS_V = 'fa fa-arrows-v';
	const ICON_ARROWS_H = 'fa fa-arrows-h';
	const ICON_BAR_CHART_O = 'fa fa-bar-chart-o';
	const ICON_BAR_CHART = 'fa fa-bar-chart';
	const ICON_TWITTER_SQUARE = 'fa fa-twitter-square';
	const ICON_FACEBOOK_SQUARE = 'fa fa-facebook-square';
	const ICON_CAMERA_RETRO = 'fa fa-camera-retro';
	const ICON_KEY = 'fa fa-key';
	const ICON_GEARS = 'fa fa-gears';
	const ICON_COGS = 'fa fa-cogs';
	const ICON_COMMENTS = 'fa fa-comments';
	const ICON_THUMBS_O_UP = 'fa fa-thumbs-o-up';
	const ICON_THUMBS_O_DOWN = 'fa fa-thumbs-o-down';
	const ICON_STAR_HALF = 'fa fa-star-half';
	const ICON_HEART_O = 'fa fa-heart-o';
	const ICON_SIGN_OUT = 'fa fa-sign-out';
	const ICON_LINKEDIN_SQUARE = 'fa fa-linkedin-square';
	const ICON_THUMB_TACK = 'fa fa-thumb-tack';
	const ICON_EXTERNAL_LINK = 'fa fa-external-link';
	const ICON_SIGN_IN = 'fa fa-sign-in';
	const ICON_TROPHY = 'fa fa-trophy';
	const ICON_GITHUB_SQUARE = 'fa fa-github-square';
	const ICON_UPLOAD = 'fa fa-upload';
	const ICON_LEMON_O = 'fa fa-lemon-o';
	const ICON_PHONE = 'fa fa-phone';
	const ICON_SQUARE_O = 'fa fa-square-o';
	const ICON_BOOKMARK_O = 'fa fa-bookmark-o';
	const ICON_PHONE_SQUARE = 'fa fa-phone-square';
	const ICON_TWITTER = 'fa fa-twitter';
	const ICON_FACEBOOK_F = 'fa fa-facebook-f';
	const ICON_FACEBOOK = 'fa fa-facebook';
	const ICON_GITHUB = 'fa fa-github';
	const ICON_UNLOCK = 'fa fa-unlock';
	const ICON_CREDIT_CARD = 'fa fa-credit-card';
	const ICON_FEED = 'fa fa-feed';
	const ICON_RSS = 'fa fa-rss';
	const ICON_HDD_O = 'fa fa-hdd-o';
	const ICON_BULLHORN = 'fa fa-bullhorn';
	const ICON_BELL = 'fa fa-bell';
	const ICON_CERTIFICATE = 'fa fa-certificate';
	const ICON_HAND_O_RIGHT = 'fa fa-hand-o-right';
	const ICON_HAND_O_LEFT = 'fa fa-hand-o-left';
	const ICON_HAND_O_UP = 'fa fa-hand-o-up';
	const ICON_HAND_O_DOWN = 'fa fa-hand-o-down';
	const ICON_ARROW_CIRCLE_LEFT = 'fa fa-arrow-circle-left';
	const ICON_ARROW_CIRCLE_RIGHT = 'fa fa-arrow-circle-right';
	const ICON_ARROW_CIRCLE_UP = 'fa fa-arrow-circle-up';
	const ICON_ARROW_CIRCLE_DOWN = 'fa fa-arrow-circle-down';
	const ICON_GLOBE = 'fa fa-globe';
	const ICON_WRENCH = 'fa fa-wrench';
	const ICON_TASKS = 'fa fa-tasks';
	const ICON_FILTER = 'fa fa-filter';
	const ICON_BRIEFCASE = 'fa fa-briefcase';
	const ICON_ARROWS_ALT = 'fa fa-arrows-alt';
	const ICON_GROUP = 'fa fa-group';
	const ICON_USERS = 'fa fa-users';
	const ICON_CHAIN = 'fa fa-chain';
	const ICON_LINK = 'fa fa-link';
	const ICON_CLOUD = 'fa fa-cloud';
	const ICON_FLASK = 'fa fa-flask';
	const ICON_CUT = 'fa fa-cut';
	const ICON_SCISSORS = 'fa fa-scissors';
	const ICON_COPY = 'fa fa-copy';
	const ICON_FILES_O = 'fa fa-files-o';
	const ICON_PAPERCLIP = 'fa fa-paperclip';
	const ICON_SAVE = 'fa fa-save';
	const ICON_FLOPPY_O = 'fa fa-floppy-o';
	const ICON_SQUARE = 'fa fa-square';
	const ICON_NAVICON = 'fa fa-navicon';
	const ICON_REORDER = 'fa fa-reorder';
	const ICON_BARS = 'fa fa-bars';
	const ICON_LIST_UL = 'fa fa-list-ul';
	const ICON_LIST_OL = 'fa fa-list-ol';
	const ICON_STRIKETHROUGH = 'fa fa-strikethrough';
	const ICON_UNDERLINE = 'fa fa-underline';
	const ICON_TABLE = 'fa fa-table';
	const ICON_MAGIC = 'fa fa-magic';
	const ICON_TRUCK = 'fa fa-truck';
	const ICON_PINTEREST = 'fa fa-pinterest';
	const ICON_PINTEREST_SQUARE = 'fa fa-pinterest-square';
	const ICON_GOOGLE_PLUS_SQUARE = 'fa fa-google-plus-square';
	const ICON_GOOGLE_PLUS = 'fa fa-google-plus';
	const ICON_MONEY = 'fa fa-money';
	const ICON_CARET_DOWN = 'fa fa-caret-down';
	const ICON_CARET_UP = 'fa fa-caret-up';
	const ICON_CARET_LEFT = 'fa fa-caret-left';
	const ICON_CARET_RIGHT = 'fa fa-caret-right';
	const ICON_COLUMNS = 'fa fa-columns';
	const ICON_UNSORTED = 'fa fa-unsorted';
	const ICON_SORT = 'fa fa-sort';
	const ICON_SORT_DOWN = 'fa fa-sort-down';
	const ICON_SORT_DESC = 'fa fa-sort-desc';
	const ICON_SORT_UP = 'fa fa-sort-up';
	const ICON_SORT_ASC = 'fa fa-sort-asc';
	const ICON_ENVELOPE = 'fa fa-envelope';
	const ICON_LINKEDIN = 'fa fa-linkedin';
	const ICON_ROTATE_LEFT = 'fa fa-rotate-left';
	const ICON_UNDO = 'fa fa-undo';
	const ICON_LEGAL = 'fa fa-legal';
	const ICON_GAVEL = 'fa fa-gavel';
	const ICON_DASHBOARD = 'fa fa-dashboard';
	const ICON_TACHOMETER = 'fa fa-tachometer';
	const ICON_COMMENT_O = 'fa fa-comment-o';
	const ICON_COMMENTS_O = 'fa fa-comments-o';
	const ICON_FLASH = 'fa fa-flash';
	const ICON_BOLT = 'fa fa-bolt';
	const ICON_SITEMAP = 'fa fa-sitemap';
	const ICON_UMBRELLA = 'fa fa-umbrella';
	const ICON_PASTE = 'fa fa-paste';
	const ICON_CLIPBOARD = 'fa fa-clipboard';
	const ICON_LIGHTBULB_O = 'fa fa-lightbulb-o';
	const ICON_EXCHANGE = 'fa fa-exchange';
	const ICON_CLOUD_DOWNLOAD = 'fa fa-cloud-download';
	const ICON_CLOUD_UPLOAD = 'fa fa-cloud-upload';
	const ICON_USER_MD = 'fa fa-user-md';
	const ICON_STETHOSCOPE = 'fa fa-stethoscope';
	const ICON_SUITCASE = 'fa fa-suitcase';
	const ICON_BELL_O = 'fa fa-bell-o';
	const ICON_COFFEE = 'fa fa-coffee';
	const ICON_CUTLERY = 'fa fa-cutlery';
	const ICON_FILE_TEXT_O = 'fa fa-file-text-o';
	const ICON_BUILDING_O = 'fa fa-building-o';
	const ICON_HOSPITAL_O = 'fa fa-hospital-o';
	const ICON_AMBULANCE = 'fa fa-ambulance';
	const ICON_MEDKIT = 'fa fa-medkit';
	const ICON_FIGHTER_JET = 'fa fa-fighter-jet';
	const ICON_BEER = 'fa fa-beer';
	const ICON_H_SQUARE = 'fa fa-h-square';
	const ICON_PLUS_SQUARE = 'fa fa-plus-square';
	const ICON_ANGLE_DOUBLE_LEFT = 'fa fa-angle-double-left';
	const ICON_ANGLE_DOUBLE_RIGHT = 'fa fa-angle-double-right';
	const ICON_ANGLE_DOUBLE_UP = 'fa fa-angle-double-up';
	const ICON_ANGLE_DOUBLE_DOWN = 'fa fa-angle-double-down';
	const ICON_ANGLE_LEFT = 'fa fa-angle-left';
	const ICON_ANGLE_RIGHT = 'fa fa-angle-right';
	const ICON_ANGLE_UP = 'fa fa-angle-up';
	const ICON_ANGLE_DOWN = 'fa fa-angle-down';
	const ICON_DESKTOP = 'fa fa-desktop';
	const ICON_LAPTOP = 'fa fa-laptop';
	const ICON_TABLET = 'fa fa-tablet';
	const ICON_MOBILE_PHONE = 'fa fa-mobile-phone';
	const ICON_MOBILE = 'fa fa-mobile';
	const ICON_CIRCLE_O = 'fa fa-circle-o';
	const ICON_QUOTE_LEFT = 'fa fa-quote-left';
	const ICON_QUOTE_RIGHT = 'fa fa-quote-right';
	const ICON_SPINNER = 'fa fa-spinner';
	const ICON_CIRCLE = 'fa fa-circle';
	const ICON_MAIL_REPLY = 'fa fa-mail-reply';
	const ICON_REPLY = 'fa fa-reply';
	const ICON_GITHUB_ALT = 'fa fa-github-alt';
	const ICON_FOLDER_O = 'fa fa-folder-o';
	const ICON_FOLDER_OPEN_O = 'fa fa-folder-open-o';
	const ICON_SMILE_O = 'fa fa-smile-o';
	const ICON_FROWN_O = 'fa fa-frown-o';
	const ICON_MEH_O = 'fa fa-meh-o';
	const ICON_GAMEPAD = 'fa fa-gamepad';
	const ICON_KEYBOARD_O = 'fa fa-keyboard-o';
	const ICON_FLAG_O = 'fa fa-flag-o';
	const ICON_FLAG_CHECKERED = 'fa fa-flag-checkered';
	const ICON_TERMINAL = 'fa fa-terminal';
	const ICON_CODE = 'fa fa-code';
	const ICON_MAIL_REPLY_ALL = 'fa fa-mail-reply-all';
	const ICON_REPLY_ALL = 'fa fa-reply-all';
	const ICON_STAR_HALF_EMPTY = 'fa fa-star-half-empty';
	const ICON_STAR_HALF_FULL = 'fa fa-star-half-full';
	const ICON_STAR_HALF_O = 'fa fa-star-half-o';
	const ICON_LOCATION_ARROW = 'fa fa-location-arrow';
	const ICON_CROP = 'fa fa-crop';
	const ICON_CODE_FORK = 'fa fa-code-fork';
	const ICON_UNLINK = 'fa fa-unlink';
	const ICON_CHAIN_BROKEN = 'fa fa-chain-broken';
	const ICON_QUESTION = 'fa fa-question';
	const ICON_INFO = 'fa fa-info';
	const ICON_EXCLAMATION = 'fa fa-exclamation';
	const ICON_SUPERSCRIPT = 'fa fa-superscript';
	const ICON_SUBSCRIPT = 'fa fa-subscript';
	const ICON_ERASER = 'fa fa-eraser';
	const ICON_PUZZLE_PIECE = 'fa fa-puzzle-piece';
	const ICON_MICROPHONE = 'fa fa-microphone';
	const ICON_MICROPHONE_SLASH = 'fa fa-microphone-slash';
	const ICON_SHIELD = 'fa fa-shield';
	const ICON_CALENDAR_O = 'fa fa-calendar-o';
	const ICON_FIRE_EXTINGUISHER = 'fa fa-fire-extinguisher';
	const ICON_ROCKET = 'fa fa-rocket';
	const ICON_MAXCDN = 'fa fa-maxcdn';
	const ICON_CHEVRON_CIRCLE_LEFT = 'fa fa-chevron-circle-left';
	const ICON_CHEVRON_CIRCLE_RIGHT = 'fa fa-chevron-circle-right';
	const ICON_CHEVRON_CIRCLE_UP = 'fa fa-chevron-circle-up';
	const ICON_CHEVRON_CIRCLE_DOWN = 'fa fa-chevron-circle-down';
	const ICON_HTML5 = 'fa fa-html5';
	const ICON_CSS3 = 'fa fa-css3';
	const ICON_ANCHOR = 'fa fa-anchor';
	const ICON_UNLOCK_ALT = 'fa fa-unlock-alt';
	const ICON_BULLSEYE = 'fa fa-bullseye';
	const ICON_ELLIPSIS_H = 'fa fa-ellipsis-h';
	const ICON_ELLIPSIS_V = 'fa fa-ellipsis-v';
	const ICON_RSS_SQUARE = 'fa fa-rss-square';
	const ICON_PLAY_CIRCLE = 'fa fa-play-circle';
	const ICON_TICKET = 'fa fa-ticket';
	const ICON_MINUS_SQUARE = 'fa fa-minus-square';
	const ICON_MINUS_SQUARE_O = 'fa fa-minus-square-o';
	const ICON_LEVEL_UP = 'fa fa-level-up';
	const ICON_LEVEL_DOWN = 'fa fa-level-down';
	const ICON_CHECK_SQUARE = 'fa fa-check-square';
	const ICON_PENCIL_SQUARE = 'fa fa-pencil-square';
	const ICON_EXTERNAL_LINK_SQUARE = 'fa fa-external-link-square';
	const ICON_SHARE_SQUARE = 'fa fa-share-square';
	const ICON_COMPASS = 'fa fa-compass';
	const ICON_TOGGLE_DOWN = 'fa fa-toggle-down';
	const ICON_CARET_SQUARE_O_DOWN = 'fa fa-caret-square-o-down';
	const ICON_TOGGLE_UP = 'fa fa-toggle-up';
	const ICON_CARET_SQUARE_O_UP = 'fa fa-caret-square-o-up';
	const ICON_TOGGLE_RIGHT = 'fa fa-toggle-right';
	const ICON_CARET_SQUARE_O_RIGHT = 'fa fa-caret-square-o-right';
	const ICON_EURO = 'fa fa-euro';
	const ICON_EUR = 'fa fa-eur';
	const ICON_GBP = 'fa fa-gbp';
	const ICON_DOLLAR = 'fa fa-dollar';
	const ICON_USD = 'fa fa-usd';
	const ICON_RUPEE = 'fa fa-rupee';
	const ICON_INR = 'fa fa-inr';
	const ICON_CNY = 'fa fa-cny';
	const ICON_RMB = 'fa fa-rmb';
	const ICON_YEN = 'fa fa-yen';
	const ICON_JPY = 'fa fa-jpy';
	const ICON_RUBLE = 'fa fa-ruble';
	const ICON_ROUBLE = 'fa fa-rouble';
	const ICON_RUB = 'fa fa-rub';
	const ICON_WON = 'fa fa-won';
	const ICON_KRW = 'fa fa-krw';
	const ICON_BITCOIN = 'fa fa-bitcoin';
	const ICON_BTC = 'fa fa-btc';
	const ICON_FILE = 'fa fa-file';
	const ICON_FILE_TEXT = 'fa fa-file-text';
	const ICON_SORT_ALPHA_ASC = 'fa fa-sort-alpha-asc';
	const ICON_SORT_ALPHA_DESC = 'fa fa-sort-alpha-desc';
	const ICON_SORT_AMOUNT_ASC = 'fa fa-sort-amount-asc';
	const ICON_SORT_AMOUNT_DESC = 'fa fa-sort-amount-desc';
	const ICON_SORT_NUMERIC_ASC = 'fa fa-sort-numeric-asc';
	const ICON_SORT_NUMERIC_DESC = 'fa fa-sort-numeric-desc';
	const ICON_THUMBS_UP = 'fa fa-thumbs-up';
	const ICON_THUMBS_DOWN = 'fa fa-thumbs-down';
	const ICON_YOUTUBE_SQUARE = 'fa fa-youtube-square';
	const ICON_YOUTUBE = 'fa fa-youtube';
	const ICON_XING = 'fa fa-xing';
	const ICON_XING_SQUARE = 'fa fa-xing-square';
	const ICON_YOUTUBE_PLAY = 'fa fa-youtube-play';
	const ICON_DROPBOX = 'fa fa-dropbox';
	const ICON_STACK_OVERFLOW = 'fa fa-stack-overflow';
	const ICON_INSTAGRAM = 'fa fa-instagram';
	const ICON_FLICKR = 'fa fa-flickr';
	const ICON_ADN = 'fa fa-adn';
	const ICON_BITBUCKET = 'fa fa-bitbucket';
	const ICON_BITBUCKET_SQUARE = 'fa fa-bitbucket-square';
	const ICON_TUMBLR = 'fa fa-tumblr';
	const ICON_TUMBLR_SQUARE = 'fa fa-tumblr-square';
	const ICON_LONG_ARROW_DOWN = 'fa fa-long-arrow-down';
	const ICON_LONG_ARROW_UP = 'fa fa-long-arrow-up';
	const ICON_LONG_ARROW_LEFT = 'fa fa-long-arrow-left';
	const ICON_LONG_ARROW_RIGHT = 'fa fa-long-arrow-right';
	const ICON_APPLE = 'fa fa-apple';
	const ICON_WINDOWS = 'fa fa-windows';
	const ICON_ANDROID = 'fa fa-android';
	const ICON_LINUX = 'fa fa-linux';
	const ICON_DRIBBBLE = 'fa fa-dribbble';
	const ICON_SKYPE = 'fa fa-skype';
	const ICON_FOURSQUARE = 'fa fa-foursquare';
	const ICON_TRELLO = 'fa fa-trello';
	const ICON_FEMALE = 'fa fa-female';
	const ICON_MALE = 'fa fa-male';
	const ICON_GITTIP = 'fa fa-gittip';
	const ICON_GRATIPAY = 'fa fa-gratipay';
	const ICON_SUN_O = 'fa fa-sun-o';
	const ICON_MOON_O = 'fa fa-moon-o';
	const ICON_ARCHIVE = 'fa fa-archive';
	const ICON_BUG = 'fa fa-bug';
	const ICON_VK = 'fa fa-vk';
	const ICON_WEIBO = 'fa fa-weibo';
	const ICON_RENREN = 'fa fa-renren';
	const ICON_PAGELINES = 'fa fa-pagelines';
	const ICON_STACK_EXCHANGE = 'fa fa-stack-exchange';
	const ICON_ARROW_CIRCLE_O_RIGHT = 'fa fa-arrow-circle-o-right';
	const ICON_ARROW_CIRCLE_O_LEFT = 'fa fa-arrow-circle-o-left';
	const ICON_TOGGLE_LEFT = 'fa fa-toggle-left';
	const ICON_CARET_SQUARE_O_LEFT = 'fa fa-caret-square-o-left';
	const ICON_DOT_CIRCLE_O = 'fa fa-dot-circle-o';
	const ICON_WHEELCHAIR = 'fa fa-wheelchair';
	const ICON_VIMEO_SQUARE = 'fa fa-vimeo-square';
	const ICON_TURKISH_LIRA = 'fa fa-turkish-lira';
	const ICON_TRY = 'fa fa-try';
	const ICON_PLUS_SQUARE_O = 'fa fa-plus-square-o';
	const ICON_SPACE_SHUTTLE = 'fa fa-space-shuttle';
	const ICON_SLACK = 'fa fa-slack';
	const ICON_ENVELOPE_SQUARE = 'fa fa-envelope-square';
	const ICON_WORDPRESS = 'fa fa-wordpress';
	const ICON_OPENID = 'fa fa-openid';
	const ICON_INSTITUTION = 'fa fa-institution';
	const ICON_BANK = 'fa fa-bank';
	const ICON_UNIVERSITY = 'fa fa-university';
	const ICON_MORTAR_BOARD = 'fa fa-mortar-board';
	const ICON_GRADUATION_CAP = 'fa fa-graduation-cap';
	const ICON_YAHOO = 'fa fa-yahoo';
	const ICON_GOOGLE = 'fa fa-google';
	const ICON_REDDIT = 'fa fa-reddit';
	const ICON_REDDIT_SQUARE = 'fa fa-reddit-square';
	const ICON_STUMBLEUPON_CIRCLE = 'fa fa-stumbleupon-circle';
	const ICON_STUMBLEUPON = 'fa fa-stumbleupon';
	const ICON_DELICIOUS = 'fa fa-delicious';
	const ICON_DIGG = 'fa fa-digg';
	const ICON_PIED_PIPER_PP = 'fa fa-pied-piper-pp';
	const ICON_PIED_PIPER_ALT = 'fa fa-pied-piper-alt';
	const ICON_DRUPAL = 'fa fa-drupal';
	const ICON_JOOMLA = 'fa fa-joomla';
	const ICON_LANGUAGE = 'fa fa-language';
	const ICON_FAX = 'fa fa-fax';
	const ICON_BUILDING = 'fa fa-building';
	const ICON_CHILD = 'fa fa-child';
	const ICON_PAW = 'fa fa-paw';
	const ICON_SPOON = 'fa fa-spoon';
	const ICON_CUBE = 'fa fa-cube';
	const ICON_CUBES = 'fa fa-cubes';
	const ICON_BEHANCE = 'fa fa-behance';
	const ICON_BEHANCE_SQUARE = 'fa fa-behance-square';
	const ICON_STEAM = 'fa fa-steam';
	const ICON_STEAM_SQUARE = 'fa fa-steam-square';
	const ICON_RECYCLE = 'fa fa-recycle';
	const ICON_AUTOMOBILE = 'fa fa-automobile';
	const ICON_CAR = 'fa fa-car';
	const ICON_CAB = 'fa fa-cab';
	const ICON_TAXI = 'fa fa-taxi';
	const ICON_TREE = 'fa fa-tree';
	const ICON_SPOTIFY = 'fa fa-spotify';
	const ICON_DEVIANTART = 'fa fa-deviantart';
	const ICON_SOUNDCLOUD = 'fa fa-soundcloud';
	const ICON_DATABASE = 'fa fa-database';
	const ICON_FILE_PDF_O = 'fa fa-file-pdf-o';
	const ICON_FILE_WORD_O = 'fa fa-file-word-o';
	const ICON_FILE_EXCEL_O = 'fa fa-file-excel-o';
	const ICON_FILE_POWERPOINT_O = 'fa fa-file-powerpoint-o';
	const ICON_FILE_PHOTO_O = 'fa fa-file-photo-o';
	const ICON_FILE_PICTURE_O = 'fa fa-file-picture-o';
	const ICON_FILE_IMAGE_O = 'fa fa-file-image-o';
	const ICON_FILE_ZIP_O = 'fa fa-file-zip-o';
	const ICON_FILE_ARCHIVE_O = 'fa fa-file-archive-o';
	const ICON_FILE_SOUND_O = 'fa fa-file-sound-o';
	const ICON_FILE_AUDIO_O = 'fa fa-file-audio-o';
	const ICON_FILE_MOVIE_O = 'fa fa-file-movie-o';
	const ICON_FILE_VIDEO_O = 'fa fa-file-video-o';
	const ICON_FILE_CODE_O = 'fa fa-file-code-o';
	const ICON_VINE = 'fa fa-vine';
	const ICON_CODEPEN = 'fa fa-codepen';
	const ICON_JSFIDDLE = 'fa fa-jsfiddle';
	const ICON_LIFE_BOUY = 'fa fa-life-bouy';
	const ICON_LIFE_BUOY = 'fa fa-life-buoy';
	const ICON_LIFE_SAVER = 'fa fa-life-saver';
	const ICON_SUPPORT = 'fa fa-support';
	const ICON_LIFE_RING = 'fa fa-life-ring';
	const ICON_CIRCLE_O_NOTCH = 'fa fa-circle-o-notch';
	const ICON_RA = 'fa fa-ra';
	const ICON_RESISTANCE = 'fa fa-resistance';
	const ICON_REBEL = 'fa fa-rebel';
	const ICON_GE = 'fa fa-ge';
	const ICON_EMPIRE = 'fa fa-empire';
	const ICON_GIT_SQUARE = 'fa fa-git-square';
	const ICON_GIT = 'fa fa-git';
	const ICON_Y_COMBINATOR_SQUARE = 'fa fa-y-combinator-square';
	const ICON_YC_SQUARE = 'fa fa-yc-square';
	const ICON_HACKER_NEWS = 'fa fa-hacker-news';
	const ICON_TENCENT_WEIBO = 'fa fa-tencent-weibo';
	const ICON_QQ = 'fa fa-qq';
	const ICON_WECHAT = 'fa fa-wechat';
	const ICON_WEIXIN = 'fa fa-weixin';
	const ICON_SEND = 'fa fa-send';
	const ICON_PAPER_PLANE = 'fa fa-paper-plane';
	const ICON_SEND_O = 'fa fa-send-o';
	const ICON_PAPER_PLANE_O = 'fa fa-paper-plane-o';
	const ICON_HISTORY = 'fa fa-history';
	const ICON_CIRCLE_THIN = 'fa fa-circle-thin';
	const ICON_HEADER = 'fa fa-header';
	const ICON_PARAGRAPH = 'fa fa-paragraph';
	const ICON_SLIDERS = 'fa fa-sliders';
	const ICON_SHARE_ALT = 'fa fa-share-alt';
	const ICON_SHARE_ALT_SQUARE = 'fa fa-share-alt-square';
	const ICON_BOMB = 'fa fa-bomb';
	const ICON_SOCCER_BALL_O = 'fa fa-soccer-ball-o';
	const ICON_FUTBOL_O = 'fa fa-futbol-o';
	const ICON_TTY = 'fa fa-tty';
	const ICON_BINOCULARS = 'fa fa-binoculars';
	const ICON_PLUG = 'fa fa-plug';
	const ICON_SLIDESHARE = 'fa fa-slideshare';
	const ICON_TWITCH = 'fa fa-twitch';
	const ICON_YELP = 'fa fa-yelp';
	const ICON_NEWSPAPER_O = 'fa fa-newspaper-o';
	const ICON_WIFI = 'fa fa-wifi';
	const ICON_CALCULATOR = 'fa fa-calculator';
	const ICON_PAYPAL = 'fa fa-paypal';
	const ICON_GOOGLE_WALLET = 'fa fa-google-wallet';
	const ICON_CC_VISA = 'fa fa-cc-visa';
	const ICON_CC_MASTERCARD = 'fa fa-cc-mastercard';
	const ICON_CC_DISCOVER = 'fa fa-cc-discover';
	const ICON_CC_AMEX = 'fa fa-cc-amex';
	const ICON_CC_PAYPAL = 'fa fa-cc-paypal';
	const ICON_CC_STRIPE = 'fa fa-cc-stripe';
	const ICON_BELL_SLASH = 'fa fa-bell-slash';
	const ICON_BELL_SLASH_O = 'fa fa-bell-slash-o';
	const ICON_TRASH = 'fa fa-trash';
	const ICON_COPYRIGHT = 'fa fa-copyright';
	const ICON_AT = 'fa fa-at';
	const ICON_EYEDROPPER = 'fa fa-eyedropper';
	const ICON_PAINT_BRUSH = 'fa fa-paint-brush';
	const ICON_BIRTHDAY_CAKE = 'fa fa-birthday-cake';
	const ICON_AREA_CHART = 'fa fa-area-chart';
	const ICON_PIE_CHART = 'fa fa-pie-chart';
	const ICON_LINE_CHART = 'fa fa-line-chart';
	const ICON_LASTFM = 'fa fa-lastfm';
	const ICON_LASTFM_SQUARE = 'fa fa-lastfm-square';
	const ICON_TOGGLE_OFF = 'fa fa-toggle-off';
	const ICON_TOGGLE_ON = 'fa fa-toggle-on';
	const ICON_BICYCLE = 'fa fa-bicycle';
	const ICON_BUS = 'fa fa-bus';
	const ICON_IOXHOST = 'fa fa-ioxhost';
	const ICON_ANGELLIST = 'fa fa-angellist';
	const ICON_CC = 'fa fa-cc';
	const ICON_SHEKEL = 'fa fa-shekel';
	const ICON_SHEQEL = 'fa fa-sheqel';
	const ICON_ILS = 'fa fa-ils';
	const ICON_MEANPATH = 'fa fa-meanpath';
	const ICON_BUYSELLADS = 'fa fa-buysellads';
	const ICON_CONNECTDEVELOP = 'fa fa-connectdevelop';
	const ICON_DASHCUBE = 'fa fa-dashcube';
	const ICON_FORUMBEE = 'fa fa-forumbee';
	const ICON_LEANPUB = 'fa fa-leanpub';
	const ICON_SELLSY = 'fa fa-sellsy';
	const ICON_SHIRTSINBULK = 'fa fa-shirtsinbulk';
	const ICON_SIMPLYBUILT = 'fa fa-simplybuilt';
	const ICON_SKYATLAS = 'fa fa-skyatlas';
	const ICON_CART_PLUS = 'fa fa-cart-plus';
	const ICON_CART_ARROW_DOWN = 'fa fa-cart-arrow-down';
	const ICON_DIAMOND = 'fa fa-diamond';
	const ICON_SHIP = 'fa fa-ship';
	const ICON_USER_SECRET = 'fa fa-user-secret';
	const ICON_MOTORCYCLE = 'fa fa-motorcycle';
	const ICON_STREET_VIEW = 'fa fa-street-view';
	const ICON_HEARTBEAT = 'fa fa-heartbeat';
	const ICON_VENUS = 'fa fa-venus';
	const ICON_MARS = 'fa fa-mars';
	const ICON_MERCURY = 'fa fa-mercury';
	const ICON_INTERSEX = 'fa fa-intersex';
	const ICON_TRANSGENDER = 'fa fa-transgender';
	const ICON_TRANSGENDER_ALT = 'fa fa-transgender-alt';
	const ICON_VENUS_DOUBLE = 'fa fa-venus-double';
	const ICON_MARS_DOUBLE = 'fa fa-mars-double';
	const ICON_VENUS_MARS = 'fa fa-venus-mars';
	const ICON_MARS_STROKE = 'fa fa-mars-stroke';
	const ICON_MARS_STROKE_V = 'fa fa-mars-stroke-v';
	const ICON_MARS_STROKE_H = 'fa fa-mars-stroke-h';
	const ICON_NEUTER = 'fa fa-neuter';
	const ICON_GENDERLESS = 'fa fa-genderless';
	const ICON_FACEBOOK_OFFICIAL = 'fa fa-facebook-official';
	const ICON_PINTEREST_P = 'fa fa-pinterest-p';
	const ICON_WHATSAPP = 'fa fa-whatsapp';
	const ICON_SERVER = 'fa fa-server';
	const ICON_USER_PLUS = 'fa fa-user-plus';
	const ICON_USER_TIMES = 'fa fa-user-times';
	const ICON_HOTEL = 'fa fa-hotel';
	const ICON_BED = 'fa fa-bed';
	const ICON_VIACOIN = 'fa fa-viacoin';
	const ICON_TRAIN = 'fa fa-train';
	const ICON_SUBWAY = 'fa fa-subway';
	const ICON_MEDIUM = 'fa fa-medium';
	const ICON_YC = 'fa fa-yc';
	const ICON_Y_COMBINATOR = 'fa fa-y-combinator';
	const ICON_OPTIN_MONSTER = 'fa fa-optin-monster';
	const ICON_OPENCART = 'fa fa-opencart';
	const ICON_EXPEDITEDSSL = 'fa fa-expeditedssl';
	const ICON_BATTERY_4 = 'fa fa-battery-4';
	const ICON_BATTERY = 'fa fa-battery';
	const ICON_BATTERY_FULL = 'fa fa-battery-full';
	const ICON_BATTERY_3 = 'fa fa-battery-3';
	const ICON_BATTERY_THREE_QUARTERS = 'fa fa-battery-three-quarters';
	const ICON_BATTERY_2 = 'fa fa-battery-2';
	const ICON_BATTERY_HALF = 'fa fa-battery-half';
	const ICON_BATTERY_1 = 'fa fa-battery-1';
	const ICON_BATTERY_QUARTER = 'fa fa-battery-quarter';
	const ICON_BATTERY_0 = 'fa fa-battery-0';
	const ICON_BATTERY_EMPTY = 'fa fa-battery-empty';
	const ICON_MOUSE_POINTER = 'fa fa-mouse-pointer';
	const ICON_I_CURSOR = 'fa fa-i-cursor';
	const ICON_OBJECT_GROUP = 'fa fa-object-group';
	const ICON_OBJECT_UNGROUP = 'fa fa-object-ungroup';
	const ICON_STICKY_NOTE = 'fa fa-sticky-note';
	const ICON_STICKY_NOTE_O = 'fa fa-sticky-note-o';
	const ICON_CC_JCB = 'fa fa-cc-jcb';
	const ICON_CC_DINERS_CLUB = 'fa fa-cc-diners-club';
	const ICON_CLONE = 'fa fa-clone';
	const ICON_BALANCE_SCALE = 'fa fa-balance-scale';
	const ICON_HOURGLASS_O = 'fa fa-hourglass-o';
	const ICON_HOURGLASS_1 = 'fa fa-hourglass-1';
	const ICON_HOURGLASS_START = 'fa fa-hourglass-start';
	const ICON_HOURGLASS_2 = 'fa fa-hourglass-2';
	const ICON_HOURGLASS_HALF = 'fa fa-hourglass-half';
	const ICON_HOURGLASS_3 = 'fa fa-hourglass-3';
	const ICON_HOURGLASS_END = 'fa fa-hourglass-end';
	const ICON_HOURGLASS = 'fa fa-hourglass';
	const ICON_HAND_GRAB_O = 'fa fa-hand-grab-o';
	const ICON_HAND_ROCK_O = 'fa fa-hand-rock-o';
	const ICON_HAND_STOP_O = 'fa fa-hand-stop-o';
	const ICON_HAND_PAPER_O = 'fa fa-hand-paper-o';
	const ICON_HAND_SCISSORS_O = 'fa fa-hand-scissors-o';
	const ICON_HAND_LIZARD_O = 'fa fa-hand-lizard-o';
	const ICON_HAND_SPOCK_O = 'fa fa-hand-spock-o';
	const ICON_HAND_POINTER_O = 'fa fa-hand-pointer-o';
	const ICON_HAND_PEACE_O = 'fa fa-hand-peace-o';
	const ICON_TRADEMARK = 'fa fa-trademark';
	const ICON_REGISTERED = 'fa fa-registered';
	const ICON_CREATIVE_COMMONS = 'fa fa-creative-commons';
	const ICON_GG = 'fa fa-gg';
	const ICON_GG_CIRCLE = 'fa fa-gg-circle';
	const ICON_TRIPADVISOR = 'fa fa-tripadvisor';
	const ICON_ODNOKLASSNIKI = 'fa fa-odnoklassniki';
	const ICON_ODNOKLASSNIKI_SQUARE = 'fa fa-odnoklassniki-square';
	const ICON_GET_POCKET = 'fa fa-get-pocket';
	const ICON_WIKIPEDIA_W = 'fa fa-wikipedia-w';
	const ICON_SAFARI = 'fa fa-safari';
	const ICON_CHROME = 'fa fa-chrome';
	const ICON_FIREFOX = 'fa fa-firefox';
	const ICON_OPERA = 'fa fa-opera';
	const ICON_INTERNET_EXPLORER = 'fa fa-internet-explorer';
	const ICON_TV = 'fa fa-tv';
	const ICON_TELEVISION = 'fa fa-television';
	const ICON_CONTAO = 'fa fa-contao';
	const ICON_500PX = 'fa fa-500px';
	const ICON_AMAZON = 'fa fa-amazon';
	const ICON_CALENDAR_PLUS_O = 'fa fa-calendar-plus-o';
	const ICON_CALENDAR_MINUS_O = 'fa fa-calendar-minus-o';
	const ICON_CALENDAR_TIMES_O = 'fa fa-calendar-times-o';
	const ICON_CALENDAR_CHECK_O = 'fa fa-calendar-check-o';
	const ICON_INDUSTRY = 'fa fa-industry';
	const ICON_MAP_PIN = 'fa fa-map-pin';
	const ICON_MAP_SIGNS = 'fa fa-map-signs';
	const ICON_MAP_O = 'fa fa-map-o';
	const ICON_MAP = 'fa fa-map';
	const ICON_COMMENTING = 'fa fa-commenting';
	const ICON_COMMENTING_O = 'fa fa-commenting-o';
	const ICON_HOUZZ = 'fa fa-houzz';
	const ICON_VIMEO = 'fa fa-vimeo';
	const ICON_BLACK_TIE = 'fa fa-black-tie';
	const ICON_FONTICONS = 'fa fa-fonticons';
	const ICON_REDDIT_ALIEN = 'fa fa-reddit-alien';
	const ICON_EDGE = 'fa fa-edge';
	const ICON_CREDIT_CARD_ALT = 'fa fa-credit-card-alt';
	const ICON_CODIEPIE = 'fa fa-codiepie';
	const ICON_MODX = 'fa fa-modx';
	const ICON_FORT_AWESOME = 'fa fa-fort-awesome';
	const ICON_USB = 'fa fa-usb';
	const ICON_PRODUCT_HUNT = 'fa fa-product-hunt';
	const ICON_MIXCLOUD = 'fa fa-mixcloud';
	const ICON_SCRIBD = 'fa fa-scribd';
	const ICON_PAUSE_CIRCLE = 'fa fa-pause-circle';
	const ICON_PAUSE_CIRCLE_O = 'fa fa-pause-circle-o';
	const ICON_STOP_CIRCLE = 'fa fa-stop-circle';
	const ICON_STOP_CIRCLE_O = 'fa fa-stop-circle-o';
	const ICON_SHOPPING_BAG = 'fa fa-shopping-bag';
	const ICON_SHOPPING_BASKET = 'fa fa-shopping-basket';
	const ICON_HASHTAG = 'fa fa-hashtag';
	const ICON_BLUETOOTH = 'fa fa-bluetooth';
	const ICON_BLUETOOTH_B = 'fa fa-bluetooth-b';
	const ICON_PERCENT = 'fa fa-percent';
	const ICON_GITLAB = 'fa fa-gitlab';
	const ICON_WPBEGINNER = 'fa fa-wpbeginner';
	const ICON_WPFORMS = 'fa fa-wpforms';
	const ICON_ENVIRA = 'fa fa-envira';
	const ICON_UNIVERSAL_ACCESS = 'fa fa-universal-access';
	const ICON_WHEELCHAIR_ALT = 'fa fa-wheelchair-alt';
	const ICON_QUESTION_CIRCLE_O = 'fa fa-question-circle-o';
	const ICON_BLIND = 'fa fa-blind';
	const ICON_AUDIO_DESCRIPTION = 'fa fa-audio-description';
	const ICON_VOLUME_CONTROL_PHONE = 'fa fa-volume-control-phone';
	const ICON_BRAILLE = 'fa fa-braille';
	const ICON_ASSISTIVE_LISTENING_SYSTEMS = 'fa fa-assistive-listening-systems';
	const ICON_ASL_INTERPRETING = 'fa fa-asl-interpreting';
	const ICON_AMERICAN_SIGN_LANGUAGE_INTERPRETING = 'fa fa-american-sign-language-interpreting';
	const ICON_DEAFNESS = 'fa fa-deafness';
	const ICON_HARD_OF_HEARING = 'fa fa-hard-of-hearing';
	const ICON_DEAF = 'fa fa-deaf';
	const ICON_GLIDE = 'fa fa-glide';
	const ICON_GLIDE_G = 'fa fa-glide-g';
	const ICON_SIGNING = 'fa fa-signing';
	const ICON_SIGN_LANGUAGE = 'fa fa-sign-language';
	const ICON_LOW_VISION = 'fa fa-low-vision';
	const ICON_VIADEO = 'fa fa-viadeo';
	const ICON_VIADEO_SQUARE = 'fa fa-viadeo-square';
	const ICON_SNAPCHAT = 'fa fa-snapchat';
	const ICON_SNAPCHAT_GHOST = 'fa fa-snapchat-ghost';
	const ICON_SNAPCHAT_SQUARE = 'fa fa-snapchat-square';
	const ICON_PIED_PIPER = 'fa fa-pied-piper';
	const ICON_FIRST_ORDER = 'fa fa-first-order';
	const ICON_YOAST = 'fa fa-yoast';
	const ICON_THEMEISLE = 'fa fa-themeisle';
	const ICON_GOOGLE_PLUS_CIRCLE = 'fa fa-google-plus-circle';
	const ICON_GOOGLE_PLUS_OFFICIAL = 'fa fa-google-plus-official';
	const ICON_FA = 'fa fa-fa';
	const ICON_FONT_AWESOME = 'fa fa-font-awesome';
	const ICON_HANDSHAKE_O = 'fa fa-handshake-o';
	const ICON_ENVELOPE_OPEN = 'fa fa-envelope-open';
	const ICON_ENVELOPE_OPEN_O = 'fa fa-envelope-open-o';
	const ICON_LINODE = 'fa fa-linode';
	const ICON_ADDRESS_BOOK = 'fa fa-address-book';
	const ICON_ADDRESS_BOOK_O = 'fa fa-address-book-o';
	const ICON_VCARD = 'fa fa-vcard';
	const ICON_ADDRESS_CARD = 'fa fa-address-card';
	const ICON_VCARD_O = 'fa fa-vcard-o';
	const ICON_ADDRESS_CARD_O = 'fa fa-address-card-o';
	const ICON_USER_CIRCLE = 'fa fa-user-circle';
	const ICON_USER_CIRCLE_O = 'fa fa-user-circle-o';
	const ICON_USER_O = 'fa fa-user-o';
	const ICON_ID_BADGE = 'fa fa-id-badge';
	const ICON_DRIVERS_LICENSE = 'fa fa-drivers-license';
	const ICON_ID_CARD = 'fa fa-id-card';
	const ICON_DRIVERS_LICENSE_O = 'fa fa-drivers-license-o';
	const ICON_ID_CARD_O = 'fa fa-id-card-o';
	const ICON_QUORA = 'fa fa-quora';
	const ICON_FREE_CODE_CAMP = 'fa fa-free-code-camp';
	const ICON_TELEGRAM = 'fa fa-telegram';
	const ICON_THERMOMETER_4 = 'fa fa-thermometer-4';
	const ICON_THERMOMETER = 'fa fa-thermometer';
	const ICON_THERMOMETER_FULL = 'fa fa-thermometer-full';
	const ICON_THERMOMETER_3 = 'fa fa-thermometer-3';
	const ICON_THERMOMETER_THREE_QUARTERS = 'fa fa-thermometer-three-quarters';
	const ICON_THERMOMETER_2 = 'fa fa-thermometer-2';
	const ICON_THERMOMETER_HALF = 'fa fa-thermometer-half';
	const ICON_THERMOMETER_1 = 'fa fa-thermometer-1';
	const ICON_THERMOMETER_QUARTER = 'fa fa-thermometer-quarter';
	const ICON_THERMOMETER_0 = 'fa fa-thermometer-0';
	const ICON_THERMOMETER_EMPTY = 'fa fa-thermometer-empty';
	const ICON_SHOWER = 'fa fa-shower';
	const ICON_BATHTUB = 'fa fa-bathtub';
	const ICON_S15 = 'fa fa-s15';
	const ICON_BATH = 'fa fa-bath';
	const ICON_PODCAST = 'fa fa-podcast';
	const ICON_WINDOW_MAXIMIZE = 'fa fa-window-maximize';
	const ICON_WINDOW_MINIMIZE = 'fa fa-window-minimize';
	const ICON_WINDOW_RESTORE = 'fa fa-window-restore';
	const ICON_TIMES_RECTANGLE = 'fa fa-times-rectangle';
	const ICON_WINDOW_CLOSE = 'fa fa-window-close';
	const ICON_TIMES_RECTANGLE_O = 'fa fa-times-rectangle-o';
	const ICON_WINDOW_CLOSE_O = 'fa fa-window-close-o';
	const ICON_BANDCAMP = 'fa fa-bandcamp';
	const ICON_GRAV = 'fa fa-grav';
	const ICON_ETSY = 'fa fa-etsy';
	const ICON_IMDB = 'fa fa-imdb';
	const ICON_RAVELRY = 'fa fa-ravelry';
	const ICON_EERCAST = 'fa fa-eercast';
	const ICON_MICROCHIP = 'fa fa-microchip';
	const ICON_SNOWFLAKE_O = 'fa fa-snowflake-o';
	const ICON_SUPERPOWERS = 'fa fa-superpowers';
	const ICON_WPEXPLORER = 'fa fa-wpexplorer';
	const ICON_MEETUP = 'fa fa-meetup';
	
	static function getAll() {
		return array(self::ICON_GLASS,
				self::ICON_MUSIC,
				self::ICON_SEARCH,
				self::ICON_ENVELOPE_O,
				self::ICON_HEART,
				self::ICON_STAR,
				self::ICON_STAR_O,
				self::ICON_USER,
				self::ICON_FILM,
				self::ICON_TH_LARGE,
				self::ICON_TH,
				self::ICON_TH_LIST,
				self::ICON_CHECK,
				self::ICON_REMOVE,
				self::ICON_CLOSE,
				self::ICON_TIMES,
				self::ICON_SEARCH_PLUS,
				self::ICON_SEARCH_MINUS,
				self::ICON_POWER_OFF,
				self::ICON_SIGNAL,
				self::ICON_GEAR,
				self::ICON_COG,
				self::ICON_TRASH_O,
				self::ICON_HOME,
				self::ICON_FILE_O,
				self::ICON_CLOCK_O,
				self::ICON_ROAD,
				self::ICON_DOWNLOAD,
				self::ICON_ARROW_CIRCLE_O_DOWN,
				self::ICON_ARROW_CIRCLE_O_UP,
				self::ICON_INBOX,
				self::ICON_PLAY_CIRCLE_O,
				self::ICON_ROTATE_RIGHT,
				self::ICON_REPEAT,
				self::ICON_REFRESH,
				self::ICON_LIST_ALT,
				self::ICON_LOCK,
				self::ICON_FLAG,
				self::ICON_HEADPHONES,
				self::ICON_VOLUME_OFF,
				self::ICON_VOLUME_DOWN,
				self::ICON_VOLUME_UP,
				self::ICON_QRCODE,
				self::ICON_BARCODE,
				self::ICON_TAG,
				self::ICON_TAGS,
				self::ICON_BOOK,
				self::ICON_BOOKMARK,
				self::ICON_PRINT,
				self::ICON_CAMERA,
				self::ICON_FONT,
				self::ICON_BOLD,
				self::ICON_ITALIC,
				self::ICON_TEXT_HEIGHT,
				self::ICON_TEXT_WIDTH,
				self::ICON_ALIGN_LEFT,
				self::ICON_ALIGN_CENTER,
				self::ICON_ALIGN_RIGHT,
				self::ICON_ALIGN_JUSTIFY,
				self::ICON_LIST,
				self::ICON_DEDENT,
				self::ICON_OUTDENT,
				self::ICON_INDENT,
				self::ICON_VIDEO_CAMERA,
				self::ICON_PHOTO,
				self::ICON_IMAGE,
				self::ICON_PICTURE_O,
				self::ICON_PENCIL,
				self::ICON_MAP_MARKER,
				self::ICON_ADJUST,
				self::ICON_TINT,
				self::ICON_EDIT,
				self::ICON_PENCIL_SQUARE_O,
				self::ICON_SHARE_SQUARE_O,
				self::ICON_CHECK_SQUARE_O,
				self::ICON_ARROWS,
				self::ICON_STEP_BACKWARD,
				self::ICON_FAST_BACKWARD,
				self::ICON_BACKWARD,
				self::ICON_PLAY,
				self::ICON_PAUSE,
				self::ICON_STOP,
				self::ICON_FORWARD,
				self::ICON_FAST_FORWARD,
				self::ICON_STEP_FORWARD,
				self::ICON_EJECT,
				self::ICON_CHEVRON_LEFT,
				self::ICON_CHEVRON_RIGHT,
				self::ICON_PLUS_CIRCLE,
				self::ICON_MINUS_CIRCLE,
				self::ICON_TIMES_CIRCLE,
				self::ICON_CHECK_CIRCLE,
				self::ICON_QUESTION_CIRCLE,
				self::ICON_INFO_CIRCLE,
				self::ICON_CROSSHAIRS,
				self::ICON_TIMES_CIRCLE_O,
				self::ICON_CHECK_CIRCLE_O,
				self::ICON_BAN,
				self::ICON_ARROW_LEFT,
				self::ICON_ARROW_RIGHT,
				self::ICON_ARROW_UP,
				self::ICON_ARROW_DOWN,
				self::ICON_MAIL_FORWARD,
				self::ICON_SHARE,
				self::ICON_EXPAND,
				self::ICON_COMPRESS,
				self::ICON_PLUS,
				self::ICON_MINUS,
				self::ICON_ASTERISK,
				self::ICON_EXCLAMATION_CIRCLE,
				self::ICON_GIFT,
				self::ICON_LEAF,
				self::ICON_FIRE,
				self::ICON_EYE,
				self::ICON_EYE_SLASH,
				self::ICON_WARNING,
				self::ICON_EXCLAMATION_TRIANGLE,
				self::ICON_PLANE,
				self::ICON_CALENDAR,
				self::ICON_RANDOM,
				self::ICON_COMMENT,
				self::ICON_MAGNET,
				self::ICON_CHEVRON_UP,
				self::ICON_CHEVRON_DOWN,
				self::ICON_RETWEET,
				self::ICON_SHOPPING_CART,
				self::ICON_FOLDER,
				self::ICON_FOLDER_OPEN,
				self::ICON_ARROWS_V,
				self::ICON_ARROWS_H,
				self::ICON_BAR_CHART_O,
				self::ICON_BAR_CHART,
				self::ICON_TWITTER_SQUARE,
				self::ICON_FACEBOOK_SQUARE,
				self::ICON_CAMERA_RETRO,
				self::ICON_KEY,
				self::ICON_GEARS,
				self::ICON_COGS,
				self::ICON_COMMENTS,
				self::ICON_THUMBS_O_UP,
				self::ICON_THUMBS_O_DOWN,
				self::ICON_STAR_HALF,
				self::ICON_HEART_O,
				self::ICON_SIGN_OUT,
				self::ICON_LINKEDIN_SQUARE,
				self::ICON_THUMB_TACK,
				self::ICON_EXTERNAL_LINK,
				self::ICON_SIGN_IN,
				self::ICON_TROPHY,
				self::ICON_GITHUB_SQUARE,
				self::ICON_UPLOAD,
				self::ICON_LEMON_O,
				self::ICON_PHONE,
				self::ICON_SQUARE_O,
				self::ICON_BOOKMARK_O,
				self::ICON_PHONE_SQUARE,
				self::ICON_TWITTER,
				self::ICON_FACEBOOK_F,
				self::ICON_FACEBOOK,
				self::ICON_GITHUB,
				self::ICON_UNLOCK,
				self::ICON_CREDIT_CARD,
				self::ICON_FEED,
				self::ICON_RSS,
				self::ICON_HDD_O,
				self::ICON_BULLHORN,
				self::ICON_BELL,
				self::ICON_CERTIFICATE,
				self::ICON_HAND_O_RIGHT,
				self::ICON_HAND_O_LEFT,
				self::ICON_HAND_O_UP,
				self::ICON_HAND_O_DOWN,
				self::ICON_ARROW_CIRCLE_LEFT,
				self::ICON_ARROW_CIRCLE_RIGHT,
				self::ICON_ARROW_CIRCLE_UP,
				self::ICON_ARROW_CIRCLE_DOWN,
				self::ICON_GLOBE,
				self::ICON_WRENCH,
				self::ICON_TASKS,
				self::ICON_FILTER,
				self::ICON_BRIEFCASE,
				self::ICON_ARROWS_ALT,
				self::ICON_GROUP,
				self::ICON_USERS,
				self::ICON_CHAIN,
				self::ICON_LINK,
				self::ICON_CLOUD,
				self::ICON_FLASK,
				self::ICON_CUT,
				self::ICON_SCISSORS,
				self::ICON_COPY,
				self::ICON_FILES_O,
				self::ICON_PAPERCLIP,
				self::ICON_SAVE,
				self::ICON_FLOPPY_O,
				self::ICON_SQUARE,
				self::ICON_NAVICON,
				self::ICON_REORDER,
				self::ICON_BARS,
				self::ICON_LIST_UL,
				self::ICON_LIST_OL,
				self::ICON_STRIKETHROUGH,
				self::ICON_UNDERLINE,
				self::ICON_TABLE,
				self::ICON_MAGIC,
				self::ICON_TRUCK,
				self::ICON_PINTEREST,
				self::ICON_PINTEREST_SQUARE,
				self::ICON_GOOGLE_PLUS_SQUARE,
				self::ICON_GOOGLE_PLUS,
				self::ICON_MONEY,
				self::ICON_CARET_DOWN,
				self::ICON_CARET_UP,
				self::ICON_CARET_LEFT,
				self::ICON_CARET_RIGHT,
				self::ICON_COLUMNS,
				self::ICON_UNSORTED,
				self::ICON_SORT,
				self::ICON_SORT_DOWN,
				self::ICON_SORT_DESC,
				self::ICON_SORT_UP,
				self::ICON_SORT_ASC,
				self::ICON_ENVELOPE,
				self::ICON_LINKEDIN,
				self::ICON_ROTATE_LEFT,
				self::ICON_UNDO,
				self::ICON_LEGAL,
				self::ICON_GAVEL,
				self::ICON_DASHBOARD,
				self::ICON_TACHOMETER,
				self::ICON_COMMENT_O,
				self::ICON_COMMENTS_O,
				self::ICON_FLASH,
				self::ICON_BOLT,
				self::ICON_SITEMAP,
				self::ICON_UMBRELLA,
				self::ICON_PASTE,
				self::ICON_CLIPBOARD,
				self::ICON_LIGHTBULB_O,
				self::ICON_EXCHANGE,
				self::ICON_CLOUD_DOWNLOAD,
				self::ICON_CLOUD_UPLOAD,
				self::ICON_USER_MD,
				self::ICON_STETHOSCOPE,
				self::ICON_SUITCASE,
				self::ICON_BELL_O,
				self::ICON_COFFEE,
				self::ICON_CUTLERY,
				self::ICON_FILE_TEXT_O,
				self::ICON_BUILDING_O,
				self::ICON_HOSPITAL_O,
				self::ICON_AMBULANCE,
				self::ICON_MEDKIT,
				self::ICON_FIGHTER_JET,
				self::ICON_BEER,
				self::ICON_H_SQUARE,
				self::ICON_PLUS_SQUARE,
				self::ICON_ANGLE_DOUBLE_LEFT,
				self::ICON_ANGLE_DOUBLE_RIGHT,
				self::ICON_ANGLE_DOUBLE_UP,
				self::ICON_ANGLE_DOUBLE_DOWN,
				self::ICON_ANGLE_LEFT,
				self::ICON_ANGLE_RIGHT,
				self::ICON_ANGLE_UP,
				self::ICON_ANGLE_DOWN,
				self::ICON_DESKTOP,
				self::ICON_LAPTOP,
				self::ICON_TABLET,
				self::ICON_MOBILE_PHONE,
				self::ICON_MOBILE,
				self::ICON_CIRCLE_O,
				self::ICON_QUOTE_LEFT,
				self::ICON_QUOTE_RIGHT,
				self::ICON_SPINNER,
				self::ICON_CIRCLE,
				self::ICON_MAIL_REPLY,
				self::ICON_REPLY,
				self::ICON_GITHUB_ALT,
				self::ICON_FOLDER_O,
				self::ICON_FOLDER_OPEN_O,
				self::ICON_SMILE_O,
				self::ICON_FROWN_O,
				self::ICON_MEH_O,
				self::ICON_GAMEPAD,
				self::ICON_KEYBOARD_O,
				self::ICON_FLAG_O,
				self::ICON_FLAG_CHECKERED,
				self::ICON_TERMINAL,
				self::ICON_CODE,
				self::ICON_MAIL_REPLY_ALL,
				self::ICON_REPLY_ALL,
				self::ICON_STAR_HALF_EMPTY,
				self::ICON_STAR_HALF_FULL,
				self::ICON_STAR_HALF_O,
				self::ICON_LOCATION_ARROW,
				self::ICON_CROP,
				self::ICON_CODE_FORK,
				self::ICON_UNLINK,
				self::ICON_CHAIN_BROKEN,
				self::ICON_QUESTION,
				self::ICON_INFO,
				self::ICON_EXCLAMATION,
				self::ICON_SUPERSCRIPT,
				self::ICON_SUBSCRIPT,
				self::ICON_ERASER,
				self::ICON_PUZZLE_PIECE,
				self::ICON_MICROPHONE,
				self::ICON_MICROPHONE_SLASH,
				self::ICON_SHIELD,
				self::ICON_CALENDAR_O,
				self::ICON_FIRE_EXTINGUISHER,
				self::ICON_ROCKET,
				self::ICON_MAXCDN,
				self::ICON_CHEVRON_CIRCLE_LEFT,
				self::ICON_CHEVRON_CIRCLE_RIGHT,
				self::ICON_CHEVRON_CIRCLE_UP,
				self::ICON_CHEVRON_CIRCLE_DOWN,
				self::ICON_HTML5,
				self::ICON_CSS3,
				self::ICON_ANCHOR,
				self::ICON_UNLOCK_ALT,
				self::ICON_BULLSEYE,
				self::ICON_ELLIPSIS_H,
				self::ICON_ELLIPSIS_V,
				self::ICON_RSS_SQUARE,
				self::ICON_PLAY_CIRCLE,
				self::ICON_TICKET,
				self::ICON_MINUS_SQUARE,
				self::ICON_MINUS_SQUARE_O,
				self::ICON_LEVEL_UP,
				self::ICON_LEVEL_DOWN,
				self::ICON_CHECK_SQUARE,
				self::ICON_PENCIL_SQUARE,
				self::ICON_EXTERNAL_LINK_SQUARE,
				self::ICON_SHARE_SQUARE,
				self::ICON_COMPASS,
				self::ICON_TOGGLE_DOWN,
				self::ICON_CARET_SQUARE_O_DOWN,
				self::ICON_TOGGLE_UP,
				self::ICON_CARET_SQUARE_O_UP,
				self::ICON_TOGGLE_RIGHT,
				self::ICON_CARET_SQUARE_O_RIGHT,
				self::ICON_EURO,
				self::ICON_EUR,
				self::ICON_GBP,
				self::ICON_DOLLAR,
				self::ICON_USD,
				self::ICON_RUPEE,
				self::ICON_INR,
				self::ICON_CNY,
				self::ICON_RMB,
				self::ICON_YEN,
				self::ICON_JPY,
				self::ICON_RUBLE,
				self::ICON_ROUBLE,
				self::ICON_RUB,
				self::ICON_WON,
				self::ICON_KRW,
				self::ICON_BITCOIN,
				self::ICON_BTC,
				self::ICON_FILE,
				self::ICON_FILE_TEXT,
				self::ICON_SORT_ALPHA_ASC,
				self::ICON_SORT_ALPHA_DESC,
				self::ICON_SORT_AMOUNT_ASC,
				self::ICON_SORT_AMOUNT_DESC,
				self::ICON_SORT_NUMERIC_ASC,
				self::ICON_SORT_NUMERIC_DESC,
				self::ICON_THUMBS_UP,
				self::ICON_THUMBS_DOWN,
				self::ICON_YOUTUBE_SQUARE,
				self::ICON_YOUTUBE,
				self::ICON_XING,
				self::ICON_XING_SQUARE,
				self::ICON_YOUTUBE_PLAY,
				self::ICON_DROPBOX,
				self::ICON_STACK_OVERFLOW,
				self::ICON_INSTAGRAM,
				self::ICON_FLICKR,
				self::ICON_ADN,
				self::ICON_BITBUCKET,
				self::ICON_BITBUCKET_SQUARE,
				self::ICON_TUMBLR,
				self::ICON_TUMBLR_SQUARE,
				self::ICON_LONG_ARROW_DOWN,
				self::ICON_LONG_ARROW_UP,
				self::ICON_LONG_ARROW_LEFT,
				self::ICON_LONG_ARROW_RIGHT,
				self::ICON_APPLE,
				self::ICON_WINDOWS,
				self::ICON_ANDROID,
				self::ICON_LINUX,
				self::ICON_DRIBBBLE,
				self::ICON_SKYPE,
				self::ICON_FOURSQUARE,
				self::ICON_TRELLO,
				self::ICON_FEMALE,
				self::ICON_MALE,
				self::ICON_GITTIP,
				self::ICON_GRATIPAY,
				self::ICON_SUN_O,
				self::ICON_MOON_O,
				self::ICON_ARCHIVE,
				self::ICON_BUG,
				self::ICON_VK,
				self::ICON_WEIBO,
				self::ICON_RENREN,
				self::ICON_PAGELINES,
				self::ICON_STACK_EXCHANGE,
				self::ICON_ARROW_CIRCLE_O_RIGHT,
				self::ICON_ARROW_CIRCLE_O_LEFT,
				self::ICON_TOGGLE_LEFT,
				self::ICON_CARET_SQUARE_O_LEFT,
				self::ICON_DOT_CIRCLE_O,
				self::ICON_WHEELCHAIR,
				self::ICON_VIMEO_SQUARE,
				self::ICON_TURKISH_LIRA,
				self::ICON_TRY,
				self::ICON_PLUS_SQUARE_O,
				self::ICON_SPACE_SHUTTLE,
				self::ICON_SLACK,
				self::ICON_ENVELOPE_SQUARE,
				self::ICON_WORDPRESS,
				self::ICON_OPENID,
				self::ICON_INSTITUTION,
				self::ICON_BANK,
				self::ICON_UNIVERSITY,
				self::ICON_MORTAR_BOARD,
				self::ICON_GRADUATION_CAP,
				self::ICON_YAHOO,
				self::ICON_GOOGLE,
				self::ICON_REDDIT,
				self::ICON_REDDIT_SQUARE,
				self::ICON_STUMBLEUPON_CIRCLE,
				self::ICON_STUMBLEUPON,
				self::ICON_DELICIOUS,
				self::ICON_DIGG,
				self::ICON_PIED_PIPER_PP,
				self::ICON_PIED_PIPER_ALT,
				self::ICON_DRUPAL,
				self::ICON_JOOMLA,
				self::ICON_LANGUAGE,
				self::ICON_FAX,
				self::ICON_BUILDING,
				self::ICON_CHILD,
				self::ICON_PAW,
				self::ICON_SPOON,
				self::ICON_CUBE,
				self::ICON_CUBES,
				self::ICON_BEHANCE,
				self::ICON_BEHANCE_SQUARE,
				self::ICON_STEAM,
				self::ICON_STEAM_SQUARE,
				self::ICON_RECYCLE,
				self::ICON_AUTOMOBILE,
				self::ICON_CAR,
				self::ICON_CAB,
				self::ICON_TAXI,
				self::ICON_TREE,
				self::ICON_SPOTIFY,
				self::ICON_DEVIANTART,
				self::ICON_SOUNDCLOUD,
				self::ICON_DATABASE,
				self::ICON_FILE_PDF_O,
				self::ICON_FILE_WORD_O,
				self::ICON_FILE_EXCEL_O,
				self::ICON_FILE_POWERPOINT_O,
				self::ICON_FILE_PHOTO_O,
				self::ICON_FILE_PICTURE_O,
				self::ICON_FILE_IMAGE_O,
				self::ICON_FILE_ZIP_O,
				self::ICON_FILE_ARCHIVE_O,
				self::ICON_FILE_SOUND_O,
				self::ICON_FILE_AUDIO_O,
				self::ICON_FILE_MOVIE_O,
				self::ICON_FILE_VIDEO_O,
				self::ICON_FILE_CODE_O,
				self::ICON_VINE,
				self::ICON_CODEPEN,
				self::ICON_JSFIDDLE,
				self::ICON_LIFE_BOUY,
				self::ICON_LIFE_BUOY,
				self::ICON_LIFE_SAVER,
				self::ICON_SUPPORT,
				self::ICON_LIFE_RING,
				self::ICON_CIRCLE_O_NOTCH,
				self::ICON_RA,
				self::ICON_RESISTANCE,
				self::ICON_REBEL,
				self::ICON_GE,
				self::ICON_EMPIRE,
				self::ICON_GIT_SQUARE,
				self::ICON_GIT,
				self::ICON_Y_COMBINATOR_SQUARE,
				self::ICON_YC_SQUARE,
				self::ICON_HACKER_NEWS,
				self::ICON_TENCENT_WEIBO,
				self::ICON_QQ,
				self::ICON_WECHAT,
				self::ICON_WEIXIN,
				self::ICON_SEND,
				self::ICON_PAPER_PLANE,
				self::ICON_SEND_O,
				self::ICON_PAPER_PLANE_O,
				self::ICON_HISTORY,
				self::ICON_CIRCLE_THIN,
				self::ICON_HEADER,
				self::ICON_PARAGRAPH,
				self::ICON_SLIDERS,
				self::ICON_SHARE_ALT,
				self::ICON_SHARE_ALT_SQUARE,
				self::ICON_BOMB,
				self::ICON_SOCCER_BALL_O,
				self::ICON_FUTBOL_O,
				self::ICON_TTY,
				self::ICON_BINOCULARS,
				self::ICON_PLUG,
				self::ICON_SLIDESHARE,
				self::ICON_TWITCH,
				self::ICON_YELP,
				self::ICON_NEWSPAPER_O,
				self::ICON_WIFI,
				self::ICON_CALCULATOR,
				self::ICON_PAYPAL,
				self::ICON_GOOGLE_WALLET,
				self::ICON_CC_VISA,
				self::ICON_CC_MASTERCARD,
				self::ICON_CC_DISCOVER,
				self::ICON_CC_AMEX,
				self::ICON_CC_PAYPAL,
				self::ICON_CC_STRIPE,
				self::ICON_BELL_SLASH,
				self::ICON_BELL_SLASH_O,
				self::ICON_TRASH,
				self::ICON_COPYRIGHT,
				self::ICON_AT,
				self::ICON_EYEDROPPER,
				self::ICON_PAINT_BRUSH,
				self::ICON_BIRTHDAY_CAKE,
				self::ICON_AREA_CHART,
				self::ICON_PIE_CHART,
				self::ICON_LINE_CHART,
				self::ICON_LASTFM,
				self::ICON_LASTFM_SQUARE,
				self::ICON_TOGGLE_OFF,
				self::ICON_TOGGLE_ON,
				self::ICON_BICYCLE,
				self::ICON_BUS,
				self::ICON_IOXHOST,
				self::ICON_ANGELLIST,
				self::ICON_CC,
				self::ICON_SHEKEL,
				self::ICON_SHEQEL,
				self::ICON_ILS,
				self::ICON_MEANPATH,
				self::ICON_BUYSELLADS,
				self::ICON_CONNECTDEVELOP,
				self::ICON_DASHCUBE,
				self::ICON_FORUMBEE,
				self::ICON_LEANPUB,
				self::ICON_SELLSY,
				self::ICON_SHIRTSINBULK,
				self::ICON_SIMPLYBUILT,
				self::ICON_SKYATLAS,
				self::ICON_CART_PLUS,
				self::ICON_CART_ARROW_DOWN,
				self::ICON_DIAMOND,
				self::ICON_SHIP,
				self::ICON_USER_SECRET,
				self::ICON_MOTORCYCLE,
				self::ICON_STREET_VIEW,
				self::ICON_HEARTBEAT,
				self::ICON_VENUS,
				self::ICON_MARS,
				self::ICON_MERCURY,
				self::ICON_INTERSEX,
				self::ICON_TRANSGENDER,
				self::ICON_TRANSGENDER_ALT,
				self::ICON_VENUS_DOUBLE,
				self::ICON_MARS_DOUBLE,
				self::ICON_VENUS_MARS,
				self::ICON_MARS_STROKE,
				self::ICON_MARS_STROKE_V,
				self::ICON_MARS_STROKE_H,
				self::ICON_NEUTER,
				self::ICON_GENDERLESS,
				self::ICON_FACEBOOK_OFFICIAL,
				self::ICON_PINTEREST_P,
				self::ICON_WHATSAPP,
				self::ICON_SERVER,
				self::ICON_USER_PLUS,
				self::ICON_USER_TIMES,
				self::ICON_HOTEL,
				self::ICON_BED,
				self::ICON_VIACOIN,
				self::ICON_TRAIN,
				self::ICON_SUBWAY,
				self::ICON_MEDIUM,
				self::ICON_YC,
				self::ICON_Y_COMBINATOR,
				self::ICON_OPTIN_MONSTER,
				self::ICON_OPENCART,
				self::ICON_EXPEDITEDSSL,
				self::ICON_BATTERY_4,
				self::ICON_BATTERY,
				self::ICON_BATTERY_FULL,
				self::ICON_BATTERY_3,
				self::ICON_BATTERY_THREE_QUARTERS,
				self::ICON_BATTERY_2,
				self::ICON_BATTERY_HALF,
				self::ICON_BATTERY_1,
				self::ICON_BATTERY_QUARTER,
				self::ICON_BATTERY_0,
				self::ICON_BATTERY_EMPTY,
				self::ICON_MOUSE_POINTER,
				self::ICON_I_CURSOR,
				self::ICON_OBJECT_GROUP,
				self::ICON_OBJECT_UNGROUP,
				self::ICON_STICKY_NOTE,
				self::ICON_STICKY_NOTE_O,
				self::ICON_CC_JCB,
				self::ICON_CC_DINERS_CLUB,
				self::ICON_CLONE,
				self::ICON_BALANCE_SCALE,
				self::ICON_HOURGLASS_O,
				self::ICON_HOURGLASS_1,
				self::ICON_HOURGLASS_START,
				self::ICON_HOURGLASS_2,
				self::ICON_HOURGLASS_HALF,
				self::ICON_HOURGLASS_3,
				self::ICON_HOURGLASS_END,
				self::ICON_HOURGLASS,
				self::ICON_HAND_GRAB_O,
				self::ICON_HAND_ROCK_O,
				self::ICON_HAND_STOP_O,
				self::ICON_HAND_PAPER_O,
				self::ICON_HAND_SCISSORS_O,
				self::ICON_HAND_LIZARD_O,
				self::ICON_HAND_SPOCK_O,
				self::ICON_HAND_POINTER_O,
				self::ICON_HAND_PEACE_O,
				self::ICON_TRADEMARK,
				self::ICON_REGISTERED,
				self::ICON_CREATIVE_COMMONS,
				self::ICON_GG,
				self::ICON_GG_CIRCLE,
				self::ICON_TRIPADVISOR,
				self::ICON_ODNOKLASSNIKI,
				self::ICON_ODNOKLASSNIKI_SQUARE,
				self::ICON_GET_POCKET,
				self::ICON_WIKIPEDIA_W,
				self::ICON_SAFARI,
				self::ICON_CHROME,
				self::ICON_FIREFOX,
				self::ICON_OPERA,
				self::ICON_INTERNET_EXPLORER,
				self::ICON_TV,
				self::ICON_TELEVISION,
				self::ICON_CONTAO,
				self::ICON_500PX,
				self::ICON_AMAZON,
				self::ICON_CALENDAR_PLUS_O,
				self::ICON_CALENDAR_MINUS_O,
				self::ICON_CALENDAR_TIMES_O,
				self::ICON_CALENDAR_CHECK_O,
				self::ICON_INDUSTRY,
				self::ICON_MAP_PIN,
				self::ICON_MAP_SIGNS,
				self::ICON_MAP_O,
				self::ICON_MAP,
				self::ICON_COMMENTING,
				self::ICON_COMMENTING_O,
				self::ICON_HOUZZ,
				self::ICON_VIMEO,
				self::ICON_BLACK_TIE,
				self::ICON_FONTICONS,
				self::ICON_REDDIT_ALIEN,
				self::ICON_EDGE,
				self::ICON_CREDIT_CARD_ALT,
				self::ICON_CODIEPIE,
				self::ICON_MODX,
				self::ICON_FORT_AWESOME,
				self::ICON_USB,
				self::ICON_PRODUCT_HUNT,
				self::ICON_MIXCLOUD,
				self::ICON_SCRIBD,
				self::ICON_PAUSE_CIRCLE,
				self::ICON_PAUSE_CIRCLE_O,
				self::ICON_STOP_CIRCLE,
				self::ICON_STOP_CIRCLE_O,
				self::ICON_SHOPPING_BAG,
				self::ICON_SHOPPING_BASKET,
				self::ICON_HASHTAG,
				self::ICON_BLUETOOTH,
				self::ICON_BLUETOOTH_B,
				self::ICON_PERCENT,
				self::ICON_GITLAB,
				self::ICON_WPBEGINNER,
				self::ICON_WPFORMS,
				self::ICON_ENVIRA,
				self::ICON_UNIVERSAL_ACCESS,
				self::ICON_WHEELCHAIR_ALT,
				self::ICON_QUESTION_CIRCLE_O,
				self::ICON_BLIND,
				self::ICON_AUDIO_DESCRIPTION,
				self::ICON_VOLUME_CONTROL_PHONE,
				self::ICON_BRAILLE,
				self::ICON_ASSISTIVE_LISTENING_SYSTEMS,
				self::ICON_ASL_INTERPRETING,
				self::ICON_AMERICAN_SIGN_LANGUAGE_INTERPRETING,
				self::ICON_DEAFNESS,
				self::ICON_HARD_OF_HEARING,
				self::ICON_DEAF,
				self::ICON_GLIDE,
				self::ICON_GLIDE_G,
				self::ICON_SIGNING,
				self::ICON_SIGN_LANGUAGE,
				self::ICON_LOW_VISION,
				self::ICON_VIADEO,
				self::ICON_VIADEO_SQUARE,
				self::ICON_SNAPCHAT,
				self::ICON_SNAPCHAT_GHOST,
				self::ICON_SNAPCHAT_SQUARE,
				self::ICON_PIED_PIPER,
				self::ICON_FIRST_ORDER,
				self::ICON_YOAST,
				self::ICON_THEMEISLE,
				self::ICON_GOOGLE_PLUS_CIRCLE,
				self::ICON_GOOGLE_PLUS_OFFICIAL,
				self::ICON_FA,
				self::ICON_FONT_AWESOME,
				self::ICON_HANDSHAKE_O,
				self::ICON_ENVELOPE_OPEN,
				self::ICON_ENVELOPE_OPEN_O,
				self::ICON_LINODE,
				self::ICON_ADDRESS_BOOK,
				self::ICON_ADDRESS_BOOK_O,
				self::ICON_VCARD,
				self::ICON_ADDRESS_CARD,
				self::ICON_VCARD_O,
				self::ICON_ADDRESS_CARD_O,
				self::ICON_USER_CIRCLE,
				self::ICON_USER_CIRCLE_O,
				self::ICON_USER_O,
				self::ICON_ID_BADGE,
				self::ICON_DRIVERS_LICENSE,
				self::ICON_ID_CARD,
				self::ICON_DRIVERS_LICENSE_O,
				self::ICON_ID_CARD_O,
				self::ICON_QUORA,
				self::ICON_FREE_CODE_CAMP,
				self::ICON_TELEGRAM,
				self::ICON_THERMOMETER_4,
				self::ICON_THERMOMETER,
				self::ICON_THERMOMETER_FULL,
				self::ICON_THERMOMETER_3,
				self::ICON_THERMOMETER_THREE_QUARTERS,
				self::ICON_THERMOMETER_2,
				self::ICON_THERMOMETER_HALF,
				self::ICON_THERMOMETER_1,
				self::ICON_THERMOMETER_QUARTER,
				self::ICON_THERMOMETER_0,
				self::ICON_THERMOMETER_EMPTY,
				self::ICON_SHOWER,
				self::ICON_BATHTUB,
				self::ICON_S15,
				self::ICON_BATH,
				self::ICON_PODCAST,
				self::ICON_WINDOW_MAXIMIZE,
				self::ICON_WINDOW_MINIMIZE,
				self::ICON_WINDOW_RESTORE,
				self::ICON_TIMES_RECTANGLE,
				self::ICON_WINDOW_CLOSE,
				self::ICON_TIMES_RECTANGLE_O,
				self::ICON_WINDOW_CLOSE_O,
				self::ICON_BANDCAMP,
				self::ICON_GRAV,
				self::ICON_ETSY,
				self::ICON_IMDB,
				self::ICON_RAVELRY,
				self::ICON_EERCAST,
				self::ICON_MICROCHIP,
				self::ICON_SNOWFLAKE_O,
				self::ICON_SUPERPOWERS,
				self::ICON_WPEXPLORER,
				self::ICON_MEETUP);
	}
}
