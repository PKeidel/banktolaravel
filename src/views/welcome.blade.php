@extends('banktolaravel::layout')

@section('head')
    <style>
        pre code {
            transition: max-height 3s linear;
            max-height: 150px;
            max-width: 100%;
        }
        pre > span {
            user-select: none;
        }
    </style>
@endsection

@section('script')
    <script>
        var req;
        var infos      = $('#infos');
        var migration  = $('#migration');
        var blades     = $('#blades');
        var routes     = $('#routes');
        var controller = $('#controller');
        var model      = $('#model');
        function toggle(ele) {
            ele = $(ele);
            const h = ele.next().css('max-height');
            ele.next().css('max-height', h === '150px' ? '1000px' : '150px');
        }
        function table_clicked(tbl) {
            if(req) req.abort();
            req = $.get('/banktolaravel/{{ $connection }}/' + tbl + '/infos').done(function(data) {
                infos.jsonViewer(data.infos);
                migration.text(data.migration);
                routes.text(data.routes);
                controller.text(data.controller);
                model.text(data.model);
                hljs.highlightBlock(migration.get(0));
                hljs.highlightBlock(infos.get(0));
                hljs.highlightBlock(routes.get(0));
                hljs.highlightBlock(controller.get(0));
                hljs.highlightBlock(model.get(0));

                blades.text('<!-- view -->\n' + data.blades.view +
                    '\n\n<!-- edit -->\n' + data.blades.edit +
                    '\n\n<!-- list -->\n' + data.blades.list);
                hljs.highlightBlock(blades.get(0));
                req = null;
            });
        }
        hljs.initHighlightingOnLoad();
    </script>
@endsection

@section('content')
    <div>Connection: {{ $connection }}</div>
    <form>
        <select name="connection">
            @foreach($connections as $c)
                <option @if($c == $connection) selected @endif>{{ $c }}</option>
            @endforeach
        </select>
        <input type="submit" value="Set Connection">
    </form>
    <br>
    <table class="table" border="" width="100%">
        <tr>
            <td valign="top" width="200px">
                <h4>Tables</h4>
                @foreach($tables as $table)
                    <li onclick="table_clicked('{{ $table }}');">{{ $table }}</li>
                @endforeach
            </td>
            <td valign="top">
                <h4>Infos 1</h4>
                <pre><span onclick="toggle(this);">toggle migration</span><code id="migration" class="php">migration</code></pre>
                <pre><span onclick="toggle(this);">toggle blades</span><code id="blades" class="html">blades</code></pre>
                <pre><span onclick="toggle(this);">toggle infos</span><code id="infos" class="json">infos</code></pre>
            </td>
            <td valign="top">
                <h4>Infos 2</h4>
                <pre><span onclick="toggle(this);">toggle routes</span><code id="routes" class="php">routes</code></pre>
                <pre><span onclick="toggle(this);">toggle controller</span><code id="controller" class="php">controller</code></pre>
                <pre><span onclick="toggle(this);">toggle model</span><code id="model" class="php">model</code></pre>
            </td>
        </tr>
    </table>
@endsection