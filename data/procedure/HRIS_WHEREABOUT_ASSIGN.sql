CREATE OR REPLACE PROCEDURE HRIS_WHEREABOUT_ASSIGN (
    p_employee_id   NUMBER,
    p_order_by      NUMBER
) AS
    v_update   NUMBER := 1;
    v_employee_id      NUMBER;
BEGIN
    BEGIN
        SELECT
            employee_id
        INTO
            v_employee_id
        FROM
            HRIS_EMP_WHEREABOUT_ASN
        WHERE
            employee_id = p_employee_id;

    EXCEPTION
        WHEN no_data_found THEN
            INSERT INTO HRIS_EMP_WHEREABOUT_ASN VALUES (
                p_employee_id,
                p_order_by
            );

            v_update := 0;
    END;

    IF
        ( v_update = 1 )
    THEN
        UPDATE HRIS_EMP_WHEREABOUT_ASN
            SET
                ORDER_BY = p_order_by
        WHERE
            employee_id = p_employee_id;

    END IF;

END;