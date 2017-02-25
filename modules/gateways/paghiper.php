<?php
    function paghiper_config()
    {
        $lsdoc = array();
        $where = array(
            "type" => array("sqltype" => "LIKE", "value" => "client")
        );
        $result = select_query("tblcustomfields", "id,fieldname", $where);
        $gsmnumber = '';
        while ($data = mysql_fetch_array($result)) {
            $lsdoc[$data['id']] = $data['fieldname'];
        }
        return array(
            'FriendlyName' => array(
                "Type" => "System",
                "Value" => "PagHiper 2017"
            ),
            'informacao' => array(
                "FriendlyName" => "<big class='label label-success'><i class='fa fa-info'></i> INFORMAÇÕES</span></big>".$_SESSION['id'],
                "Description" => "<i class='fa fa-check'></i> Esse módulo foi criado pela <a href='http://compulabs.com.br' target='_blank'><img src='http://i.imgur.com/25FCm6q.png' /></a><br/> <i class='fa fa-check'></i> Todos os créditos são direcionados a desenvolvedora.<br/> <i class='fa fa-check'></i> Esse módulo é <b>grátis</b>, portanto a venda do mesmo é proibida.<br/> <i class='fa fa-check'></i> Caso efetue melhoras no código, o mesmo deverá ter o código aberto e mentido o nome da desenvolvedora.<br/> <i class='fa fa-check'></i> Licença: <a href='http://compulabs.com.br/licencas/?t=modulo-paghiper-whmcs' target='_blank'>pode ser encontrada aqui</a></div>"
            ),
            'email' => array(
                "FriendlyName" => "Email",
                "Type" => "text",
                "Size" => "100",
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Email do PagHiper, que irá receber o pagamento.</small>"
            ),
            'key' => array(
                "FriendlyName" => "Token",
                "Type" => "text",
                "Size" => "100",
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Token de acesso a API do PagHiper. Pode ser encontrado em sua conta PagHiper.</small>"
            ),
            'doc' => array(
                'FriendlyName' => 'Campo do CPF/CNPJ',
                'Type' => 'dropdown',
                'Options' => $lsdoc,
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Campo personalizado que contém o CPF/CNPJ do cliente.</small>"
            ),
            "botaopagar" => array(
                "FriendlyName" => "Texto do botão",
                "Type" => "text",
                "Size" => "70",
                "Default" => "Gerar boleto",
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Texto do botão de pagamento. HTML Habilitado.</small>"
            ),
            "abrirauto" => array(
                "FriendlyName" => "Abrir boleto ao abrir fatura?",
                'Type' => 'dropdown',
                'Options' => array("0"=>"Não","1"=>"Sim"),
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Quando o cliente abrir a fatura, o boleto será mostrado na tela automaticamente.</small>"
            ),
            "taxa" => array(
                "FriendlyName" => "Taxa por boleto",
                "Type" => "text",
                "Size" => "3",
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Insira o valor que você paga por boleto compensado. Use o ponto (.) para separar as casas decimais.</small>"
            ),
            "repassar" => array(
                "FriendlyName" => "Repassar taxa ao cliente?",
                'Type' => 'dropdown',
                'Options' => array("0"=>"Não","1"=>"Sim"),
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Será somado o valor da taxa ao boleto.</small>"
            ),
            "juros" => array(
                "FriendlyName" => "Juros por atraso?",
                'Type' => 'dropdown',
                'Options' => array("0"=>"Não","1"=>"Sim, %","2"=>"Sim, fixo"),
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Aplicar juros por atraso? Lebre-se que é aplicado a cada dia.</small>"
            ),
            "valorjuros" => array(
                "FriendlyName" => "Multa por atraso?",
                "Type" => "text",
                "Size" => "5",
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Quanto aplicar de juros por atrado?<br/><i class='fa fa-warning'></i> Se estiver usando %, coloque apenas o número, exemplo: <b>3</b>, que corresponde a 3%.<br/><i class='fa fa-warning'></i> Se estiver usando fixo, coloque o valor a ser aplicado, separa as casas decimais com ponto (.), exemplo: <b>0.80</b>, 80 centavos.<br/><i class='fa fa-warning'></i> Lembre-se que essa taxa é aplicado a cada dia, então, se você colocou uma taxa de 80 centavos e o cliente atrasou 3 dias, então será somado R$2,40 ao valor total do boleto.</small>"
            ),
            "vencimento" => array(
                "FriendlyName" => "Vencimento",
                'Type' => 'dropdown',
                'Options' => array("2"=>"2 Dias","3"=>"3 Dias","4"=>"4 Dias","5"=>"5 Dias","6"=>"6 Dias","7"=>"7 Dias"),
                "Description" => "<br/><small><i class='fa fa-info-circle'></i> Quantos dias somar ao vencimento a partir da data da geração do boleto</small>"
            ),
        );
    }
    if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
        header("access-control-allow-origin: *");
        require "../../init.php";
        $whmcs->load_function("gateway");
        $whmcs->load_function("invoice");
        $GATEWAY = getGatewayVariables('paghiper');
        $token = $GATEWAY['key'];
        $valorOriginal = $_POST['valorOriginal'];
        $valorLoja = $_POST['valorLoja'];
        $valorTotal = $_POST['valorTotal'];
        $status = $_POST['status'];
        $idTransacao = $_POST['idTransacao'];
        $idPlataforma = $_POST['idPlataforma'];
        $post = "idTransacao=$idTransacao" .
        "&status=$status" .
        "&codRetorno=$codRetorno" .
        "&valorOriginal=$valorOriginal" .
        "&valorLoja=$valorLoja" .
        "&token=$token";
        $enderecoPost = "https://www.paghiper.com/checkout/confirm/"; 
        ob_start();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $enderecoPost);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resposta = curl_exec($ch);
        curl_close($ch);
        $confirmado = (strcmp ($resposta, "VERIFICADO") == 0);
        if ($confirmado) {
            if($status == "Aprovado")
            {
                logTransaction($GATEWAY['name'], print_r($_POST, true) . print_r($xml_not, true), 'Successful');
                $invoiceid = checkCbInvoiceID($_POST['idPlataforma'], $GATEWAY["name"]);
                checkCbTransID($_POST['idPlataforma']);
                $valor = (float)$valorTotal - $valorOriginal;
                $taxa = (float) $GATEWAY['taxa'];
                addInvoicePayment($invoiceid, $_POST['idPlataforma'], $valor, $taxa, 'paghiper');
            }
        }
    }

    function httpPost($url,$params)
    {
        $postData = '';
        foreach($params as $k => $v) { $postData .= $k . '='.$v.'&'; }
        $postData = rtrim($postData, '&');
        $ch = curl_init();   
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        $output=curl_exec($ch); 
        curl_close($ch);
        return $output; 
    }
    function get_tag( $attr, $value, $xml ) {

        $attr = preg_quote($attr);
        $value = preg_quote($value);

        $tag_regex = '/<div[^>]*'.$attr.'="'.$value.'">(.*?)<\\/div>/si';

        preg_match($tag_regex,
        $xml,
        $matches);
        return $matches[1];
    }
    function paghiper_link($params)
    {
        $query = mysql_query("SELECT duedate FROM tblinvoices WHERE id = '".$params['invoiceid']."';");
        $row = mysql_fetch_array($query);

        $query2 = mysql_query("SELECT * FROM tblclients WHERE email = '".$params['clientdetails']['email']."';");
        $row2 = mysql_fetch_array($query2);

        $qru = mysql_query("SELECT * FROM tblcustomfieldsvalues WHERE relid = '".$row2['id']."' AND fieldid = '".$params['doc']."';");
        $rwu = mysql_fetch_array($qru);

        if(!isset( $rwu['value']))
        {
            return '<div class="alert alert-danger">Você precisa atualizar sua informação de CPF/CNPJ na área do cliente para poder gerar esse boleto.</div>';
        }

        $doc = str_replace("-", "", str_replace(".", "", str_replace("/", "", str_replace(",", "", str_replace(" ", "", $rwu['value'])))));

        if(strlen($doc) == 11)
        {
            $docx1 = "cpf";
            $docx2 = $doc;
        }
        else if(strlen($doc) == 14)
        {
            $docx1 = "cnpj";
            $docx2 = $doc;
        }

        $vencimentoinv = $row['duedate'];
        $hoje = date("Y-m-d");

        if(strtotime($hoje) == strtotime($vencimentoinv))
        {
            $vencimentofim = $params['vencimento'];
        }
        else if(strtotime($hoje) > strtotime($vencimentoinv))
        {
            $vencimentofim = $params['vencimento'];
        }
        if(strtotime($hoje) >= strtotime($vencimentoinv))
        {
            $diferenca = strtotime($vencimentoinv) - strtotime($hoje);
            $vencimentofim = floor($diferenca / (60 * 60 * 24));
        }

        $valor = $params['amount'];

        if($params['juros'] == "1" || $params['juros'] == "2")
        {
            $jrhj = new DateTime($hoje);
            $jrft = new DateTime($row['duedate']);
            $dias = (int)$intervalo->d;

            if($dias > 0)
            {
                $intervalo = $jrhj->diff($jrft);

                if($params['juros'] == "1")
                {
                    $valor = $valor + ((($valor/100) * $params['valorjuros']) * $dias);
                }
                else if($params['juros'] == "2")
                {
                    $valor = $valor + ((float)$params['valorjuros'] * $dias);
                }
            }
        }

        if($params['repassar'] == "1")
        {
            $valor += (float) $params['taxa'];
        }

        $paramsboleto = array(
           "email_loja" => $params['email'],
           "urlRetorno" => $params['systemurl'].'/modules/gateways/'.basename(__FILE__),
           "tipoBoleto" => "A4",
           "vencimentoBoleto" => $vencimentofim,
           "id_plataforma" => $params['invoiceid'],
           $docx1 => $docx2,
           "produto_codigo_1" => $params['invoiceid'],
           "produto_valor_1" => number_format($valor, 2, '.', ''),
           "produto_descricao_1" => "Fatura #".$params['invoiceid'],
           "produto_qtde_1" => "1",
           "email" => $params['clientdetails']['email'],
           "nome" => $params['clientdetails']['firstname'].' '.$params['clientdetails']['lastname'], 
           "pagamento" => "pagamento"
        );

        $boleto = httpPost("https://www.paghiper.com/checkout/",$paramsboleto);

        $linha1 = get_tag('id', 'DadosBoleto', $boleto);
        $kl1 = strpos($linha1, "{");
        $kl2 = (int)strpos($linha1, "}") - (int)$kl1 + 1;
        $dadosboleto = json_decode(substr($linha1,$kl1,$kl2));
        return ($params['linha']=="1"?'<div class="form-group"><label><i class="fa fa-keyboard-o"></i> Linha digitável</label><input type="text" id="linha" onfocus="this.select();" onmouseup="return false;" readonly="true" value="'.$dadosboleto->linhaDigitavel.'" class="form-control"></div>':null)."<span onclick='abrirboleto()' class='btn btn-block btn-primary'>".$params['botaopagar']."</span><div class='hidden' id='boleto'>".$boleto."</div><script src='https://code.jquery.com/jquery-1.12.4.min.js'integrity='sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ='crossorigin='anonymous'></script><script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js' integrity='sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS' crossorigin='anonymous'></script><script>function abrirboleto(){var w = window.open('', '', 'width=800, height=500, scrollbars=yes');var html = $('#boleto').html(); $(w.document.body).html(html);}".($params['abrirauto']==true ? "\$(document).ready(function() {\$('body').html(\$('#boleto').html());});" : null ).'</script>';
    }
?>
