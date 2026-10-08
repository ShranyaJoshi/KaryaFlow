package main

import (
	"encoding/json"
	"fmt"
	"github.com/hyperledger/fabric-contract-api-go/contractapi"
)

type SmartContract struct {
	contractapi.Contract
}

type EscrowAgreement struct {
	InvoiceID   string  `json:"invoice_id"`
	Amount      float64 `json:"amount"`
	BuyerSign   bool    `json:"buyer_sign"`
	VendorSign  bool    `json:"vendor_sign"`
	Status      string  `json:"status"`
}

func (s *SmartContract) InitEscrow(ctx contractapi.TransactionContextInterface, invoiceID string, amount float64) error {
	escrow := EscrowAgreement{
		InvoiceID:  invoiceID,
		Amount:     amount,
		BuyerSign:  false,
		VendorSign: false,
		Status:     "PENDING",
	}
	bytes, _ := json.Marshal(escrow)
	return ctx.GetStub().PutState(invoiceID, bytes)
}

func (s *SmartContract) SignParty(ctx contractapi.TransactionContextInterface, invoiceID string, party string) (string, error) {
	bytes, err := ctx.GetStub().GetState(invoiceID)
	if err != nil || bytes == nil {
		return "", fmt.Errorf("escrow agreement not found")
	}

	var escrow EscrowAgreement
	json.Unmarshal(bytes, &escrow)

	if party == "buyer" {
		escrow.BuyerSign = true
	} else if party == "vendor" {
		escrow.VendorSign = true
	}

	if escrow.BuyerSign && escrow.VendorSign {
		escrow.Status = "COMMITTED"
	}

	updated, _ := json.Marshal(escrow)
	ctx.GetStub().PutState(invoiceID, updated)
	return escrow.Status, nil
}

func main() {
	cc, err := contractapi.NewChaincode(&SmartContract{})
	if err != nil {
		fmt.Printf("Error creating chaincode: %s", err.Error())
		return
	}
	if err := cc.Start(); err != nil {
		fmt.Printf("Error starting chaincode: %s", err.Error())
	}
}