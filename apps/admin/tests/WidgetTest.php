<?php

use App\Core\Widget;

/**
 * Basic render
 */
$html = Widget::render('div', ['class' => 'box'], 'Hello');

assertEquals(
  '<div class="box">Hello</div>',
  $html,
  'Widget should render simple HTML correctly'
);

/**
 * Nested widget
 */
$html = Widget::render(
  'h2',
  null,
  Widget::render('h3', null, 'Nested')
);

assertEquals(
  '<h2><h3>Nested</h3></h2>',
  $html,
  'Widget should render nested widget'
);

/**
 * Multiple children
 */
$html = Widget::render(
  'p',
  null,
  [
    'Hello',
    Widget::render('br/'),
    'World'
  ]
);

assertEquals(
  '<p>Hello<br />World</p>',
  $html,
  'Widget should render mixed children correctly'
);

echo "WidgetTest passed\n";
