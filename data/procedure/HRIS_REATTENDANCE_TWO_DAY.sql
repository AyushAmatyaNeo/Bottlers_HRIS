create or replace PROCEDURE HRIS_REATTENDANCE_TWO_DAY(
    P_ATTENDANCE_DT    DATE,
    P_EMPLOYEE_ID      NUMBER,
    P_SHIFT_ID         NUMBER,
    P_MONTH_START_DATE DATE,
    P_MONTH_END_DATE   DATE)
AS
  V_LATE_START_TIME    TIMESTAMP;
  V_EARLY_END_TIME     TIMESTAMP;
  V_HALF_INTERVAL      DATE;
  v_NEXT_HALF_INTERVAL DATE;
  V_IN_TIME HRIS_ATTENDANCE_DETAIL.IN_TIME%TYPE;
  V_OUT_TIME HRIS_ATTENDANCE_DETAIL.OUT_TIME%TYPE;
  V_DIFF_IN_MIN NUMBER;
  --
  V_OVERALL_STATUS HRIS_ATTENDANCE_DETAIL.OVERALL_STATUS%TYPE;
  V_LATE_STATUS HRIS_ATTENDANCE_DETAIL.LATE_STATUS%TYPE:='N';
  V_HALFDAY_FLAG HRIS_ATTENDANCE_DETAIL.HALFDAY_FLAG%TYPE;
  V_HALFDAY_PERIOD HRIS_ATTENDANCE_DETAIL.HALFDAY_PERIOD%TYPE;
  V_GRACE_PERIOD HRIS_ATTENDANCE_DETAIL.GRACE_PERIOD%TYPE;
  V_LATE_COUNT NUMBER;
  --
  V_LATE_IN HRIS_SHIFTS.LATE_IN%TYPE;
  V_EARLY_OUT HRIS_SHIFTS.EARLY_OUT%TYPE;
  V_ADJUSTED_START_TIME HRIS_SHIFT_ADJUSTMENT.START_TIME%TYPE:=NULL;
  V_ADJUSTED_END_TIME HRIS_SHIFT_ADJUSTMENT.END_TIME%TYPE    :=NULL;
BEGIN
  SELECT OVERALL_STATUS,
    LATE_STATUS,
    HALFDAY_FLAG,
    V_HALFDAY_PERIOD,
    V_GRACE_PERIOD
  INTO V_OVERALL_STATUS,
    V_LATE_STATUS,
    V_HALFDAY_FLAG,
    V_HALFDAY_PERIOD,
    V_GRACE_PERIOD
  FROM HRIS_ATTENDANCE_DETAIL
  WHERE ATTENDANCE_DT = P_ATTENDANCE_DT
  AND EMPLOYEE_ID     =P_EMPLOYEE_ID;
  --
  SELECT S.START_TIME+((1/1440)*NVL(S.LATE_IN,0)),
    S.END_TIME       -((1/1440)*NVL(S.EARLY_OUT,0))
  INTO V_LATE_START_TIME,
    V_EARLY_END_TIME
  FROM HRIS_SHIFTS S
  WHERE S.SHIFT_ID=P_SHIFT_ID ;
  --
  V_LATE_START_TIME := TO_DATE(TO_CHAR(P_ATTENDANCE_DT,'DD-MON-YYYY')||' '||TO_CHAR(V_LATE_START_TIME,'HH:MI AM'),'DD-MON-YYYY HH:MI AM');
  V_EARLY_END_TIME  := TO_DATE(TO_CHAR(P_ATTENDANCE_DT,'DD-MON-YYYY')||' '|| TO_CHAR(V_EARLY_END_TIME,'HH:MI AM'),'DD-MON-YYYY HH:MI AM');
  --
  SELECT V_EARLY_END_TIME + (V_LATE_START_TIME -V_EARLY_END_TIME)/2
  INTO V_HALF_INTERVAL
  FROM DUAL;
  V_NEXT_HALF_INTERVAL:=V_HALF_INTERVAL+1;
  --
  HRIS_ATTD_IN_OUT(P_EMPLOYEE_ID,V_HALF_INTERVAL,V_NEXT_HALF_INTERVAL,V_IN_TIME,V_OUT_TIME);
  --
  IF V_IN_TIME IS NULL AND V_OUT_TIME IS NULL THEN
    RETURN;
  END IF ;
  --
  IF V_IN_TIME  = V_OUT_TIME THEN
    V_OUT_TIME := NULL;
  END IF;
  --
  IF V_IN_TIME IS NOT NULL AND V_OUT_TIME IS NOT NULL THEN
    SELECT SUM(ABS(EXTRACT( HOUR FROM DIFF ))*60 + ABS(EXTRACT( MINUTE FROM DIFF )))
    INTO V_DIFF_IN_MIN
    FROM
      (SELECT V_OUT_TIME -V_IN_TIME AS DIFF FROM DUAL
      ) ;
  END IF;
  BEGIN
    IF V_HALFDAY_PERIOD IS NOT NULL THEN
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        (
        CASE
          WHEN V_HALFDAY_PERIOD ='F'
          THEN S.HALF_DAY_IN_TIME
          ELSE S.START_TIME
        END )+((1/1440)*NVL(S.LATE_IN,0)),
        (
        CASE
          WHEN V_HALFDAY_PERIOD ='F'
          THEN S.END_TIME
          ELSE S.HALF_DAY_OUT_TIME
        END ) -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID    =P_SHIFT_ID ;
    ELSIF V_GRACE_PERIOD IS NOT NULL THEN
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        (
        CASE
          WHEN V_GRACE_PERIOD ='E'
          THEN S.GRACE_START_TIME
          ELSE S.START_TIME
        END)+((1/1440)*NVL(S.LATE_IN,0)),
        (
        CASE
          WHEN V_GRACE_PERIOD ='E'
          THEN S.END_TIME
          ELSE S.GRACE_END_TIME
        END) -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID=P_SHIFT_ID ;
    ELSE
      SELECT S.LATE_IN,
        S.EARLY_OUT,
        S.START_TIME+((1/1440)*NVL(S.LATE_IN,0)),
        S.END_TIME  -((1/1440)*NVL(S.EARLY_OUT,0))
      INTO V_LATE_IN,
        V_EARLY_OUT,
        V_LATE_START_TIME,
        V_EARLY_END_TIME
      FROM HRIS_SHIFTS S
      WHERE S.SHIFT_ID=P_SHIFT_ID ;
    END IF;
    V_LATE_START_TIME := TO_DATE(TO_CHAR(P_ATTENDANCE_DT,'DD-MON-YYYY')||' '||TO_CHAR(V_LATE_START_TIME,'HH:MI AM'),'DD-MON-YYYY HH:MI AM');
    V_EARLY_END_TIME  := TO_DATE(TO_CHAR(P_ATTENDANCE_DT+1,'DD-MON-YYYY')||' '|| TO_CHAR(V_EARLY_END_TIME,'HH:MI AM'),'DD-MON-YYYY HH:MI AM');
    --
  EXCEPTION
  WHEN NO_DATA_FOUND THEN
    RAISE_APPLICATION_ERROR(-20344, 'SHIFT WITH SHIFT_ID => '|| P_SHIFT_ID ||' NOT FOUND.');
  END;
  --   CHECK FOR ADJUSTED SHIFT
  BEGIN
    SELECT SA.START_TIME,
      SA.END_TIME
    INTO V_ADJUSTED_START_TIME,
      V_ADJUSTED_END_TIME
    FROM HRIS_SHIFT_ADJUSTMENT SA
    JOIN HRIS_EMPLOYEE_SHIFT_ADJUSTMENT ESA
    ON (SA.ADJUSTMENT_ID=ESA.ADJUSTMENT_ID)
    WHERE (TRUNC(P_ATTENDANCE_DT) BETWEEN TRUNC(SA.ADJUSTMENT_START_DATE) AND TRUNC(SA.ADJUSTMENT_END_DATE) )
    AND ESA.EMPLOYEE_ID       =P_EMPLOYEE_ID;
    IF(V_ADJUSTED_START_TIME IS NOT NULL) THEN
      V_LATE_START_TIME      :=V_ADJUSTED_START_TIME;
      V_LATE_START_TIME      := V_LATE_START_TIME+((1/1440)*NVL(V_LATE_IN,0));
    END IF;
    IF(V_ADJUSTED_END_TIME IS NOT NULL) THEN
      V_EARLY_END_TIME     :=V_ADJUSTED_END_TIME;
      V_EARLY_END_TIME     := V_EARLY_END_TIME -((1/1440)*NVL(V_EARLY_OUT,0));
    END IF;
    V_LATE_START_TIME := TO_DATE(TO_CHAR(P_ATTENDANCE_DT,'DD-MON-YYYY')||' '||TO_CHAR(V_LATE_START_TIME,'HH:MI AM'),'DD-MON-YYYY HH:MI AM');
    V_EARLY_END_TIME  := TO_DATE(TO_CHAR(P_ATTENDANCE_DT+1,'DD-MON-YYYY')||' '|| TO_CHAR(V_EARLY_END_TIME,'HH:MI AM'),'DD-MON-YYYY HH:MI AM');
    --
  EXCEPTION
  WHEN NO_DATA_FOUND THEN
    DBMS_OUTPUT.PUT_LINE('NO ADJUSTMENT FOUND FOR EMPLOYEE =>'|| P_EMPLOYEE_ID || 'ON THE DATE'||P_ATTENDANCE_DT);
  END;
  --      END FOR CHECK FOR ADJUSTED_SHIFT
  IF(V_OVERALL_STATUS     ='DO') THEN
    V_OVERALL_STATUS     :='WD';
  ELSIF (V_OVERALL_STATUS ='HD') THEN
    V_OVERALL_STATUS     :='WH';
  ELSIF (V_OVERALL_STATUS ='LV') THEN
    NULL;
  ELSIF (V_OVERALL_STATUS ='TV') THEN
    NULL;
  ELSIF (V_OVERALL_STATUS ='TN') THEN
    NULL;
  ELSIF(V_HALFDAY_FLAG    ='Y' AND V_HALFDAY_PERIOD IS NOT NULL) OR V_GRACE_PERIOD IS NOT NULL THEN
    V_OVERALL_STATUS     :='LP';
  ELSIF (V_OVERALL_STATUS = 'AB') THEN
    V_OVERALL_STATUS     :='PR';
  END IF;
  --
  IF (V_IN_TIME   IS NOT NULL) AND (V_LATE_START_TIME<V_IN_TIME) THEN
    V_LATE_STATUS :='L';
  END IF;
  --
  IF (V_OUT_TIME     IS NOT NULL) AND (V_EARLY_END_TIME>V_OUT_TIME) THEN
    IF (V_LATE_STATUS = 'L') THEN
      V_LATE_STATUS  :='B';
    ELSE
      V_LATE_STATUS :='E';
    END IF;
  END IF;
  --
  IF V_IN_TIME      IS NOT NULL AND V_OUT_TIME IS NULL THEN
    IF V_LATE_STATUS ='L' THEN
      V_LATE_STATUS := 'Y';
    ELSE
      V_LATE_STATUS := 'X';
    END IF;
  END IF;
  --
  IF V_IN_TIME IS NULL AND V_OUT_TIME IS NOT NULL THEN
    --    CHANGE WHEN NEW VALUE IS ADDED
    IF V_LATE_STATUS ='E' THEN
      V_LATE_STATUS := 'Y';
    ELSE
      V_LATE_STATUS := 'X';
    END IF;
  END IF;
  --
  SELECT COUNT(*)
  INTO V_LATE_COUNT
  FROM HRIS_ATTENDANCE_DETAIL
  WHERE EMPLOYEE_ID = P_EMPLOYEE_ID
  AND (ATTENDANCE_DT BETWEEN P_MONTH_START_DATE AND P_ATTENDANCE_DT )
  AND OVERALL_STATUS           IN ('PR','LA')
  AND LATE_STATUS              IN ('E','L','Y') ;
  IF V_LATE_STATUS             IN ('E','L','Y') THEN
    V_LATE_COUNT       := V_LATE_COUNT+1;
    IF V_LATE_COUNT    != 0 AND MOD(V_LATE_COUNT,4)=0 THEN
      V_OVERALL_STATUS := 'LA';
    END IF;
  END IF;
  --
  IF V_LATE_STATUS   ='B' AND V_OVERALL_STATUS='PR' THEN
    V_OVERALL_STATUS:='BA';
  END IF;
  --
  UPDATE HRIS_ATTENDANCE_DETAIL
  SET IN_TIME         = V_IN_TIME,
    OUT_TIME          =V_OUT_TIME,
    OVERALL_STATUS    = V_OVERALL_STATUS,
    LATE_STATUS       = V_LATE_STATUS,
    TOTAL_HOUR        = V_DIFF_IN_MIN
  WHERE ATTENDANCE_DT = TRUNC(P_ATTENDANCE_DT)
  AND EMPLOYEE_ID     = P_EMPLOYEE_ID;
END;