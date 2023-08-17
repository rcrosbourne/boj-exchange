import * as React from "react"
import {CaretSortIcon, CheckIcon,} from "@radix-ui/react-icons"

import {cn} from "@/lib/utils"
import {Button} from "@/Components/ShadcnUI/Button"
import {Command, CommandEmpty, CommandInput, CommandItem, CommandList,} from "@/Components/ShadcnUI/Command"
import {Popover, PopoverContent, PopoverTrigger,} from "@/Components/ShadcnUI/Popover";

type PopoverTriggerProps = React.ComponentPropsWithoutRef<typeof PopoverTrigger>

interface CurrencySwitcher extends PopoverTriggerProps {
  supportedCurrencies: string[];
  defaultCurrency: string;
  selectedCurrency: string;
  setSelectedCurrency: (currency: string) => void;
}

export default function TeamSwitcher({className, supportedCurrencies, defaultCurrency, selectedCurrency, setSelectedCurrency}: CurrencySwitcher) {
  const [open, setOpen] = React.useState(false)
  return (
      <Popover open={open} onOpenChange={setOpen}>
        <PopoverTrigger asChild>
          <Button
              variant="outline"
              role="combobox"
              aria-expanded={open}
              aria-label="Select a currency"
              className={cn("w-[200px] justify-between", className)}
          >
            {selectedCurrency}
            <CaretSortIcon className="ml-auto h-4 w-4 shrink-0 opacity-50"/>
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-[200px] p-0">
          <Command>
            <CommandList>
              <CommandInput placeholder="Search currency..."/>
              <CommandEmpty>No currency found.</CommandEmpty>
              {supportedCurrencies.map((currency) => (
                  <CommandItem
                      key={currency}
                      onSelect={() => {
                        setSelectedCurrency(currency)
                        setOpen(false)
                      }}
                      className="text-sm"
                  >
                    {currency}
                    <CheckIcon
                        className={cn(
                            "ml-auto h-4 w-4",
                            selectedCurrency === currency
                                ? "opacity-100"
                                : "opacity-0"
                        )}
                    />
                  </CommandItem>
              ))}
            </CommandList>
          </Command>
        </PopoverContent>
      </Popover>
  )
}
