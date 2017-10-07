<?php

define("SQL_FATURA",
        "SELECT 
            f.id, 
            f.emissao, 
            f.vencimento, 
            coalesce(f.valor, ((SELECT sum(valor) FROM movimento m 
                                INNER JOIN movimentosFatura mf ON mf.movimento = m.id 
                                where m.operacao = 'DEBITO' AND mf.fatura = f.id) - 
                                coalesce((SELECT sum(valor) FROM movimento m 
                                INNER JOIN movimentosFatura mf ON mf.movimento = m.id 
                                where m.operacao = 'CREDITO' AND mf.fatura = f.id),0))) as valor, 
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
        FROM fatura f 
        LEFT JOIN contaBancaria c ON (c.id = f.ContaBancaria AND c.usuario = f.usuario)
        LEFT JOIN formaPagamento fp ON (fp.id = f.formaPagamento AND fp.usuario = f.usuario)
        INNER JOIN cartaoCredito cr ON (cr.id = f.cartaoCredito AND cr.usuario = f.usuario)
        WHERE cartaoCredito = :creditCard AND 
        exists (SELECT 1 
                FROM movimentosFatura mf
                where mf.fatura = f.id) 
        order by vencimento desc"); 


define("SQL_MOVIMENTO",
            "SELECT m.id, 
                    m.descricao, 
                    m.usuario, 
                    m.emissao, 
                    m.vencimento, 
                    m.valor, 
                    m.status, 
                    m.operacao, 
                    m.movimentoOrigem,
                    m.parcela, 
                    m.hashParcelas, 
                    m.hashTransferencia, 
                    fl.id as idFinalidade, 
                    fl.descricao as descricaoFinalidade,
                    c.id as idContaBancaria, 
                    c.descricao as descricaoContaBancaria , 
                    c.numero as numeroContaBancaria ,
                    c.digito as digitoContaBancaria , 
                    f.id as idFornecedor, 
                    f.descricao as descricaoFornecedor,
                    cr.id as idCartaoCredito, 
                    cr.descricao as descricaoCartaoCredito, 
                    cr.limite as limite, 
                    cr.dataFatura as dataFatura, 
                    cr.dataFechamento as dataFechamento,
                    ft.id as idFatura, 
                    ft.mesReferencia as mesReferencia, 
                    ft.valor as valorFatura, 
                    ft.valorPagamento as valorPagamentoFatura,
                    fp.id as idFormaPagamento, 
                    fp.descricao as descricaoFormaPagamento, 
                    fp.sigla as siglaFormaPagamento 
            FROM movimento m 
                LEFT JOIN contaBancaria c ON (c.id = m.ContaBancaria AND c.usuario = m.usuario)
                LEFT JOIN fornecedor f ON (f.id = m.fornecedor AND f.usuario = m.usuario)
                LEFT JOIN formaPagamento fp ON (fp.id = m.formaPagamento AND fp.usuario = m.usuario)
                LEFT JOIN cartaoCredito cr ON (cr.id = m.cartaoCredito AND cr.usuario = m.usuario)
                LEFT JOIN fatura ft ON (ft.id = m.fatura AND ft.usuario = m.usuario)
                INNER JOIN finalidade fl ON (fl.id = m.finalidade AND fl.usuario = m.usuario) "); 

define("PREVIOUS_BALANCE", 
            "SELECT 
                operacao, 
                valor 
            FROM movimento 
            where usuario = :user 
            AND hashTransferencia = '' 
            AND fatura is NULL 
            AND substring(vencimento, 1,6) < :period");

define("PREVIOUS_BALANCE_BANK_ACCOUNT",
            "SELECT 
                operacao, 
                valor 
            FROM movimento 
            where substring(vencimento, 1,6) < :period 
            AND contaBancaria = :bankAccount");
define("INVOICE_BY_PERIOD_REFERENCE",
            "SELECT id, 
                status 
            FROM fatura 
            WHERE mesReferencia = :period 
            AND cartaoCredito = :creditCard");

define("MOVEMENT_CLOSED_INVOICE", 
            "SELECT
                f.id, 
                f.status 
            FROM movimentosFatura mf 
            INNER JOIN fatura f ON (f.id = mf. fatura) 
            WHERE mf.movimento = :movement");
define("MOVEMENT_IN_INVOICE", 
            "SELECT 
                f.id,
                f.status 
            FROM movimentosFatura mf 
            INNER JOIN fatura f ON (f.id = mf. fatura) 
            WHERE mf.movimento = :movement");
