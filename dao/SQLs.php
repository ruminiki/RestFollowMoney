<?php

define("SQL_FATURA",
        "select 
            f.id, 
            f.emissao, 
            f.vencimento, 
            coalesce(f.valor, ((select sum(valor) from movimento m 
                                inner join movimentosFatura mf on mf.movimento = m.id 
                                where m.operacao = 'DEBITO' and mf.fatura = f.id) - 
                                coalesce((select sum(valor) from movimento m 
                                inner join movimentosFatura mf on mf.movimento = m.id 
                                where m.operacao = 'CREDITO' and mf.fatura = f.id),0))) as valor, 
            f.valorPagamento, 
            f.usuario as usuario, 
            f.mesReferencia as mesReferencia, 
            f.status as status, 
            c.id as idContaBancaria, 
            c.descricao as descricaoContaBancaria, 
            c.numero as numeroContaBancaria, 
            c.digito as digitoContaBancaria,
            cr.id as idCartaoCredito, 
            cr.descricao as descricaoCartaoCredito,
            cr.limite as limite, 
            cr.dataFatura as dataFatura, 
            cr.dataFechamento as dataFechamento,
            fp.id as idFormaPagamento, 
            fp.descricao as descricaoFormaPagamento, 
            fp.sigla as siglaFormaPagamento 
        from fatura f 
        left join contaBancaria c on (c.id = f.contaBancaria and c.usuario = f.usuario)
        left join formaPagamento fp on (fp.id = f.formaPagamento and fp.usuario = f.usuario)
        inner join cartaoCredito cr on (cr.id = f.cartaoCredito and cr.usuario = f.usuario)
        WHERE cartaoCredito = :creditCard and 
        exists (select 1 
                from movimentosFatura mf
                where mf.fatura = f.id) 
        order by vencimento desc"); 