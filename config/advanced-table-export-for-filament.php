<?php

return [
  'default_format' => 'xlsx',
  'time_format' => 'M_d_Y-H_i',
  'default_page_orientation' => 'landscape',
  'disable_additional_columns' => false,
  'disable_filter_columns' => false,
  'disable_file_name' => false,
  'disable_file_name_prefix' => false,
  'disable_preview' => false,
  'action_icon' => 'heroicon-o-arrow-down-on-square',
  'preview_icon' => 'heroicon-o-eye',
  'export_icon' => 'heroicon-o-arrow-down-on-square',
  'print_icon' => 'heroicon-o-printer',
  'cancel_icon' => 'heroicon-o-x-circle',
  'preview_per_page' => 25,
  'max_pdf_rows' => 200,
  'max_export_rows' => 2000,
  'pdf_renderer' => env('ADVANCED_TABLE_EXPORT_PDF_RENDERER', 'null'),
];
