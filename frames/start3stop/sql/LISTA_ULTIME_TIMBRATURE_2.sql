CREATE view [dbo].[LISTA_ULTIME_TIMBRATURE] AS
SELECT

t2.* 

FROM tb_off_operai AS t1 

INNER JOIN 
    (SELECT
		t1.ind_chiuso,
        t2.*
        
        FROM
        
            (SELECT
                t1.cod_operaio,
				t2.ind_chiuso,
                MAX(t1.dat_ora_fine) AS massimo 
            
                FROM of_riltem as t1
				
				LEFT JOIN gn_movtes as t2 on t1.num_rif_movimento=t2.num_rif_movimento
                
                GROUP BY t1.cod_operaio,t2.ind_chiuso
            
            ) AS t1
        
        LEFT JOIN of_riltem AS t2 ON t1.massimo=t2.dat_ora_fine AND t1.cod_operaio=t2.cod_operaio AND t1.ind_chiuso='N'
    ) AS t2
    
ON t1.cod_operaio=t2.cod_operaio
