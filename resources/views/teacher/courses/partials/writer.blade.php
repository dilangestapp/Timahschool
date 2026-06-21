@php
    $field = $field ?? 'content_html';
    $target = $target ?? '#'.$field;
    $value = $value ?? '';
    $placeholder = $placeholder ?? 'Rédigez votre cours ici...';
@endphp
<div class="course-word-shell">
    <div class="course-word-toolbar">
        <select data-cw-change="format"><option value="p">Normal</option><option value="h1">Titre 1</option><option value="h2">Titre 2</option><option value="h3">Titre 3</option><option value="blockquote">Citation</option></select>
        <select data-cw-change="fontName"><option value="Arial">Arial</option><option value="Calibri">Calibri</option><option value="Times New Roman">Times New Roman</option><option value="Georgia">Georgia</option><option value="Verdana">Verdana</option><option value="Tahoma">Tahoma</option><option value="Courier New">Courier New</option></select>
        <select data-cw-change="fontSize"><option value="2">12</option><option value="3" selected>16</option><option value="4">18</option><option value="5">24</option><option value="6">32</option><option value="7">48</option></select>
        <select data-cw-change="lineHeight"><option value="1.2">Interligne 1.2</option><option value="1.5" selected>Interligne 1.5</option><option value="1.7">Interligne 1.7</option><option value="2">Interligne 2</option></select>
        <span class="course-word-sep"></span>
        <button type="button" data-cw-cmd="undo">Annuler</button><button type="button" data-cw-cmd="redo">Rétablir</button><button type="button" data-cw-cmd="bold">Gras</button><button type="button" data-cw-cmd="italic">Italique</button><button type="button" data-cw-cmd="underline">Souligné</button><button type="button" data-cw-cmd="strikeThrough">Barré</button>
        <input type="color" data-cw-change="foreColor" value="#111827" title="Couleur"><input type="color" data-cw-change="backColor" value="#fff3bf" title="Surlignage">
        <span class="course-word-sep"></span>
        <button type="button" data-cw-cmd="justifyLeft">Gauche</button><button type="button" data-cw-cmd="justifyCenter">Centre</button><button type="button" data-cw-cmd="justifyRight">Droite</button><button type="button" data-cw-cmd="justifyFull">Justifier</button><button type="button" data-cw-cmd="insertUnorderedList">Puces</button><button type="button" data-cw-cmd="insertOrderedList">Numéros</button><button type="button" data-cw-cmd="outdent">- Retrait</button><button type="button" data-cw-cmd="indent">+ Retrait</button>
        <span class="course-word-sep"></span>
        <button type="button" data-cw-cmd="link">Lien</button><button type="button" data-cw-cmd="table">Tableau</button><button type="button" data-cw-cmd="pagebreak">Séparation</button><button type="button" data-cw-cmd="removeFormat">Nettoyer</button><button type="button" data-cw-cmd="fullscreen">Plein écran</button>
    </div>
    <div class="course-word-page-wrap"><div class="course-word-page" contenteditable="true" data-target="{{ $target }}" data-placeholder="{{ $placeholder }}">{!! $value !!}</div></div>
</div>
<textarea id="{{ $field }}" name="{{ $field }}" hidden>{!! $value !!}</textarea>
