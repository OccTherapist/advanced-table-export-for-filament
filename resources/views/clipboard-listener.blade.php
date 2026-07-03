<div
    x-data
    x-on:advanced-table-export-copy-to-clipboard.window="window.navigator.clipboard.writeText($event.detail.content)"
></div>
