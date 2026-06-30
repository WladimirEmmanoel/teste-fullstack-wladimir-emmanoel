/************ 
* QUERY A
*************/
SELECT
    RANK() OVER (
        ORDER BY (
            SUM(CASE WHEN o.status = 2 THEN o.total_value ELSE 0 END)
            -
            SUM(CASE WHEN o.status = 4 THEN o.total_value ELSE 0 END)
        ) DESC
    ) AS ranking,

    a.username AS nome_do_afiliado,

    COUNT(o.id) AS quant_pedidos,

    SUM(
        CASE
            WHEN o.status = 2 THEN o.total_value
            ELSE 0
        END
    ) AS receita_bruta,

    SUM(
        CASE
            WHEN o.status = 4 THEN o.total_value
            ELSE 0
        END
    ) AS valor_reembolsado,

    (
        SUM(
            CASE
                WHEN o.status = 2 THEN o.total_value
                ELSE 0
            END
        )
        -
        SUM(
            CASE
                WHEN o.status = 4 THEN o.total_value
                ELSE 0
            END
        )
    ) AS receita_liquida

FROM affiliates a

JOIN orders o
    ON o.affiliate_id = a.id

WHERE o.status IN (2, 4)

GROUP BY
    a.id,
    a.username

ORDER BY receita_liquida DESC

LIMIT 10;

/************ 
* QUERY B
*************/

SELECT
    COUNT(o.id) AS total_orders,
    SUM(
        CASE
            WHEN o.status = 2 THEN 1
            ELSE 0
        END
    ) AS pedidos_aprovados,
    SUM(
        CASE
            WHEN o.status = 3 THEN 1
            ELSE 0
        END
    ) AS pedidos_cancelados,
    ROUND(
        COALESCE(
            SUM(CASE WHEN o.status = 2 THEN 1 ELSE 0 END)
            / NULLIF(COUNT(o.id), 0),
            0
        ) * 100,
        2
    ) AS taxa_de_aprovacao
FROM orders as o;


/************ 
* QUERY C
*************/

SELECT 
    o.affiliate_id,
    DATE(o.created_at),
    o.total_value,
    COUNT(o.id) as pedido_duplicado,
    GROUP_CONCAT(o.id ORDER BY o.id) AS order_ids
FROM orders o
GROUP BY
    o.affiliate_id,
    DATE(o.created_at),
    o.total_value
HAVING COUNT(*) > 1;
