<?php
require 'vendor/autoload.php';

use Web3\Web3;
use Web3\Providers\HttpAsyncProvider;


// Connect to Ethereum node
$web3 = new Web3(new HttpAsyncProvider('https://eth-mainnet.g.alchemy.com/v2/swE9yoWrnP9EzbOKdPsJD2Hk0yb3-kDr'));


// Function to convert hexadecimal to decimal
function hexToDec($hex) {
    return base_convert($hex, 16, 10);
}

// Function to convert Wei to Ether
function weiToEther($wei) {
    return bcdiv($wei, bcpow('10', '18'), 18); // 1 Ether = 10^18 Wei
}

// Function to convert Wei to Gwei
function weiToGwei($wei) {
    return bcdiv($wei, bcpow('10', '9'), 9); // 1 Gwei = 10^9 Wei
}

// Function to get pending transactions
function getPendingTransactions($web3) {
    $pendingTransactions = [];

   // await
   $promise = $web3->eth->getBlockByNumber('pending', true, function ($err, $block) use (&$pendingTransactions) {
        if ($err !== null) {
            echo 'Error: ' . htmlspecialchars($err->getMessage());
            return;
        }

        if ($block) {
            foreach ($block->transactions as $tx) {
                $amountInWei = hexToDec($tx->value);
                $gasPriceInWei = hexToDec($tx->gasPrice);

                $pendingTransactions[] = [
                    'origin' => $tx->from,
                    'contract' => $tx->to,
                    'amount' => weiToEther($amountInWei), // Convert amount to ETH
                    'gas' => hexToDec($tx->gas),
                    'gasPrice' => weiToGwei($gasPriceInWei) // Convert gas price to Gwei
                ];
            }
        }
    });

    await($promise);

    return $pendingTransactions;
}

// Fetch pending transactions
$transactions = getPendingTransactions($web3);

// Display results
echo '<h1>Pending Transactions</h1>';
echo '<table border="1">';
echo '<tr><th>Origin</th><th>Contract</th><th>Amount (ETH)</th><th>Gas</th><th>Gas Price (Gwei)</th></tr>';
foreach ($transactions as $tx) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($tx['origin']) . '</td>';
    echo '<td>' . htmlspecialchars($tx['contract']) . '</td>';
    echo '<td>' . htmlspecialchars($tx['amount']) . '</td>'; // Amount in ETH
    echo '<td>' . htmlspecialchars($tx['gas']) . '</td>'; // Gas
    echo '<td>' . htmlspecialchars($tx['gasPrice']) . '</td>'; // Gas price in Gwei
    echo '</tr>';
}
echo '</table>';
?>
