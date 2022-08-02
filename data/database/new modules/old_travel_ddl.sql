--------------------------------------------------------
--  File created - Wednesday-December-27-2017
--------------------------------------------------------
--------------------------------------------------------
--  DDL for Table HRIS_EMPLOYEE_TRAVEL_REQUEST
--------------------------------------------------------
CREATE TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST"
  (
    "TRAVEL_ID"      NUMBER(7,0),
    "EMPLOYEE_ID"    NUMBER(7,0),
    "REQUESTED_DATE" DATE DEFAULT SYSDATE,
    "FROM_DATE"      DATE,
    "TO_DATE"        DATE,
    "DESTINATION"    VARCHAR2(255 BYTE),
    "PURPOSE"        VARCHAR2(255 BYTE),
    "REQUESTED_TYPE" CHAR(2 BYTE),
    "REQUESTED_AMOUNT" FLOAT(126),
    "REMARKS"             VARCHAR2(255 BYTE),
    "STATUS"              VARCHAR2(2 BYTE),
    "RECOMMENDED_BY"      NUMBER(7,0),
    "RECOMMENDED_DATE"    DATE,
    "RECOMMENDED_REMARKS" VARCHAR2(255 BYTE),
    "APPROVED_BY"         NUMBER(7,0),
    "APPROVED_DATE"       DATE,
    "APPROVED_REMARKS"    VARCHAR2(255 BYTE),
    "TRAVEL_CODE"         VARCHAR2(15 BYTE),
    "REFERENCE_TRAVEL_ID" NUMBER(6,0),
    "TRANSPORT_TYPE"      CHAR(2 BYTE),
    "DEPARTURE_DATE"      DATE,
    "RETURNED_DATE"       DATE
  );
--------------------------------------------------------
--  DDL for Index EMP_TRAVEL_ID_PK
--------------------------------------------------------
CREATE UNIQUE INDEX "EMP_TRAVEL_ID_PK" ON "HRIS_EMPLOYEE_TRAVEL_REQUEST"
  (
    "TRAVEL_ID"
  )
  ;
  --------------------------------------------------------
  --  Constraints for Table HRIS_EMPLOYEE_TRAVEL_REQUEST
  --------------------------------------------------------
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" ADD CONSTRAINT "EMP_TRAVEL_ID_PK" PRIMARY KEY ("TRAVEL_ID") ENABLE;
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("TRAVEL_ID" NOT NULL ENABLE) ;
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("EMPLOYEE_ID" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("REQUESTED_DATE" NOT NULL ENABLE) ;
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("FROM_DATE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("TO_DATE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("DESTINATION" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("REQUESTED_TYPE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" MODIFY ("STATUS" NOT NULL ENABLE);
  --------------------------------------------------------
  --  Ref Constraints for Table HRIS_EMPLOYEE_TRAVEL_REQUEST
  --------------------------------------------------------
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" ADD CONSTRAINT "EMP_TRAVEL_EMP_ID_FK" FOREIGN KEY ("EMPLOYEE_ID") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" ADD CONSTRAINT "EMP_TRVL_ID_REF_TRAVEL_ID_FK" FOREIGN KEY ("REFERENCE_TRAVEL_ID") REFERENCES "HRIS_EMPLOYEE_TRAVEL_REQUEST" ("TRAVEL_ID") DISABLE;
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" ADD CONSTRAINT "RQ_TRVL_APRV_BY_FK" FOREIGN KEY ("APPROVED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
  ALTER TABLE "HRIS_EMPLOYEE_TRAVEL_REQUEST" ADD CONSTRAINT "RQ_TRVL_RECMD_BY_FK" FOREIGN KEY ("RECOMMENDED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
  --------------------------------------------------------
  --  File created - Wednesday-December-27-2017
  --------------------------------------------------------
  --------------------------------------------------------
  --  DDL for Table HRIS_TRAVEL_SUBSTITUTE
  --------------------------------------------------------
  CREATE TABLE "HRIS_TRAVEL_SUBSTITUTE"
    (
      "TRAVEL_ID"     NUMBER(12,0),
      "EMPLOYEE_ID"   NUMBER(7,0),
      "REMARKS"       VARCHAR2(400 BYTE) DEFAULT NULL,
      "CREATED_BY"    NUMBER(7,0),
      "CREATED_DATE"  DATE DEFAULT SYSDATE,
      "APPROVED_FLAG" CHAR(1 BYTE) DEFAULT NULL,
      "APPROVED_DATE" DATE,
      "STATUS"        CHAR(1 BYTE) DEFAULT 'N'
    );
  --------------------------------------------------------
  --  Constraints for Table HRIS_TRAVEL_SUBSTITUTE
  --------------------------------------------------------
  ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" MODIFY ("TRAVEL_ID" NOT NULL ENABLE) ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" MODIFY ("EMPLOYEE_ID" NOT NULL ENABLE);
  ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" MODIFY ("CREATED_BY" NOT NULL ENABLE) ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" MODIFY ("CREATED_DATE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" ADD CHECK (APPROVED_FLAG IN ('Y','N')) ENABLE ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" ADD CHECK (STATUS IN ('E','D')) ENABLE;
  --------------------------------------------------------
  --  Ref Constraints for Table HRIS_TRAVEL_SUBSTITUTE
  --------------------------------------------------------
  ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" ADD CONSTRAINT "FK_TRAVELSUB_EMP_EMP_ID_1" FOREIGN KEY ("EMPLOYEE_ID") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
  ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" ADD CONSTRAINT "FK_TRAVELSUB_EMP_EMP_ID_2" FOREIGN KEY ("CREATED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE ;
  ALTER TABLE "HRIS_TRAVEL_SUBSTITUTE" ADD CONSTRAINT "FK_TRAVELSUB_TREQUEST_ID" FOREIGN KEY ("TRAVEL_ID") REFERENCES "HRIS_EMPLOYEE_TRAVEL_REQUEST" ("TRAVEL_ID") DISABLE;
  --------------------------------------------------------
  --  File created - Wednesday-December-27-2017
  --------------------------------------------------------
  --------------------------------------------------------
  --  DDL for Table HRIS_EMP_TRAVEL_EXPENSE_DTL
  --------------------------------------------------------
  CREATE TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL"
    (
      "ID"                NUMBER(7,0),
      "TRAVEL_ID"         NUMBER(7,0),
      "DEPARTURE_DATE"    DATE,
      "DEPARTURE_TIME"    TIMESTAMP (6),
      "DEPARTURE_PLACE"   VARCHAR2(255 BYTE),
      "DESTINATION_DATE"  DATE,
      "DESTINATION_TIME"  TIMESTAMP (6),
      "DESTINATION_PLACE" VARCHAR2(255 BYTE),
      "TRANSPORT_TYPE"    VARCHAR2(255 BYTE),
      "FARE" FLOAT(126),
      "ALLOWANCE" FLOAT(126),
      "LOCAL_CONVEYENCE" FLOAT(126),
      "MISC_EXPENSES" FLOAT(126),
      "TOTAL_AMOUNT" FLOAT(126),
      "REMARKS"               VARCHAR2(255 BYTE),
      "CREATED_BY"            NUMBER(7,0),
      "CREATED_DATE"          DATE DEFAULT SYSDATE,
      "MODIFIED_BY"           NUMBER(7,0),
      "MODIFIED_DATE"         DATE,
      "STATUS"                CHAR(1 BYTE),
      "MISC_EXPENSES_FLAG"    CHAR(1 BYTE),
      "LOCAL_CONVEYENCE_FLAG" CHAR(1 BYTE),
      "ALLOWANCE_FLAG"        CHAR(1 BYTE),
      "FARE_FLAG"             CHAR(1 BYTE)
    );
  --------------------------------------------------------
  --  DDL for Index PK_TRL_EXP_DTL
  --------------------------------------------------------
CREATE UNIQUE INDEX "PK_TRL_EXP_DTL" ON "HRIS_EMP_TRAVEL_EXPENSE_DTL"
  (
    "ID"
  )
  ;
  --------------------------------------------------------
  --  Constraints for Table HRIS_EMP_TRAVEL_EXPENSE_DTL
  --------------------------------------------------------
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CONSTRAINT "CHEK_TRANS" CHECK (TRANSPORT_TYPE IN ('AP','OV','TI','BS')) ENABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CONSTRAINT "PK_TRL_EXP_DTL" PRIMARY KEY ("ID") ENABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CHECK (FARE_FLAG             IN ('Y','N')) ENABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CHECK (ALLOWANCE_FLAG        IN ('Y','N')) ENABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CHECK (LOCAL_CONVEYENCE_FLAG IN ('Y','N')) ENABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CHECK (MISC_EXPENSES_FLAG    IN ('Y','N')) ENABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("TOTAL_AMOUNT" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("FARE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("TRANSPORT_TYPE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("DESTINATION_PLACE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("DESTINATION_TIME" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("DESTINATION_DATE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("DEPARTURE_PLACE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("DEPARTURE_TIME" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ( "DEPARTURE_DATE" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ( "TRAVEL_ID" NOT NULL ENABLE);
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" MODIFY ("ID" NOT NULL ENABLE);
  --------------------------------------------------------
  --  Ref Constraints for Table HRIS_EMP_TRAVEL_EXPENSE_DTL
  --------------------------------------------------------
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CONSTRAINT "FK_TRL_EXP_DTL" FOREIGN KEY ("TRAVEL_ID") REFERENCES "HRIS_EMPLOYEE_TRAVEL_REQUEST" ("TRAVEL_ID") DISABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CONSTRAINT "FK_TRL_EXP_DTL_EMP_EMP_ID" FOREIGN KEY ("CREATED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
  ALTER TABLE "HRIS_EMP_TRAVEL_EXPENSE_DTL" ADD CONSTRAINT "FK_TRL_EXP_DTL_EMP_EMP_ID2" FOREIGN KEY ("MODIFIED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;


ALTER TABLE HRIS_EMPLOYEE_TRAVEL_REQUEST ADD VOUCHER_NO VARCHAR2(255 BYTE);