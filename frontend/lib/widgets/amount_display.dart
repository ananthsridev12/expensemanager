import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

final _inrFormat = NumberFormat.currency(
  locale: 'en_IN',
  symbol: '\u20B9',
  decimalDigits: 2,
);

String formatINR(double amount) => _inrFormat.format(amount);

class AmountDisplay extends StatelessWidget {
  final double amount;
  final TextStyle? style;
  final bool colorize;

  const AmountDisplay({
    super.key,
    required this.amount,
    this.style,
    this.colorize = false,
  });

  @override
  Widget build(BuildContext context) {
    final text = formatINR(amount.abs());
    final prefix = amount < 0 ? '-' : '';
    Color? color;
    if (colorize) {
      color = amount >= 0 ? Colors.green.shade700 : Colors.red.shade700;
    }
    return Text(
      '$prefix$text',
      style: (style ?? const TextStyle()).copyWith(color: color),
    );
  }
}
